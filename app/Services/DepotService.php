<?php

namespace App\Services;

use App\Models\ArticleDepot;
use App\Models\Depot;
use App\Models\DepotHistorique;
use App\Models\Paiement;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepotService
{
    private const TRANSITIONS = [
        'depose'        => ['en_traitement', 'annule'],
        'en_traitement' => ['pret', 'annule'],
        'pret'          => ['en_traitement', 'retire', 'annule'],
        'retire'        => [],
        'annule'        => [],
    ];

    public const ROLES_CAISSE = ['admin', 'gerant', 'caissier'];

    public function creer(array $data, User $user): Depot
    {
        return DB::transaction(function () use ($data, $user) {
            $express   = (bool) ($data['express'] ?? false);
            $lignes    = [];
            $sousTotal = 0;

            foreach (array_values($data['articles']) as $i => $a) {
                // Le prix vient TOUJOURS de la grille tarifaire, jamais du formulaire
                $tarif = Tarif::where('type_article_id', $a['type_article_id'])
                    ->where('service_id', $a['service_id'])
                    ->first();

                if (! $tarif) {
                    throw ValidationException::withMessages([
                        "articles.$i.service_id" => 'Aucun tarif défini pour cet article et ce service.',
                    ]);
                }

                $prix       = (int) $tarif->prix + ($express ? (int) $tarif->prix_express : 0);
                $sousTotal += $prix * (int) $a['quantite'];
                $lignes[]   = [
                    'type_article_id' => $a['type_article_id'],
                    'service_id'      => $a['service_id'],
                    'quantite'        => (int) $a['quantite'],
                    'prix_unitaire'   => $prix,
                ];
            }

            $remise = (int) ($data['remise'] ?? 0);
            if ($remise > $sousTotal) {
                throw ValidationException::withMessages(['remise' => 'La remise dépasse le montant des articles.']);
            }

            $net  = $sousTotal - $remise;
            $paye = (int) ($data['montant_paye'] ?? 0);
            if ($paye > $net) {
                throw ValidationException::withMessages(['montant_paye' => 'Le montant payé dépasse le total à payer.']);
            }

            $depot = Depot::create([
                'pressing_id'         => $user->pressing_id,
                'numero_ticket'       => $this->prochainNumero($user->pressing_id),
                'client_id'           => $data['client_id'],
                'user_id'             => $user->id,
                'date_depot'          => now(),
                'date_retrait_prevue' => $data['date_retrait_prevue'],
                'statut'              => 'depose',
                'express'             => $express,
                'montant_total'       => $net,
                'remise'              => $remise,
                'montant_paye'        => 0,
                'reste_a_payer'       => $net,
                'observations'        => $data['observations'] ?? null,
            ]);

            foreach ($lignes as $ligne) {
                ArticleDepot::create($ligne + ['depot_id' => $depot->id]);
            }

            $this->historiser($depot, 'depose', $user, 'Dépôt enregistré');

            if ($paye > 0) {
                $this->encaisser($depot, $paye, $data['mode_paiement'] ?? 'especes', $user, $data['reference'] ?? null);
            }

            return $depot->refresh();
        });
    }

    public function encaisser(Depot $depot, int $montant, string $mode, User $user, ?string $reference = null): Paiement
    {
        if (! in_array($user->role, self::ROLES_CAISSE, true)) {
            throw ValidationException::withMessages(['montant_paye_ajout' => "Votre rôle ne permet pas d'encaisser un paiement."]);
        }

        return DB::transaction(function () use ($depot, $montant, $mode, $user, $reference) {
            $depot = Depot::lockForUpdate()->findOrFail($depot->id);

            if ($depot->statut === 'annule') {
                throw ValidationException::withMessages(['montant_paye_ajout' => 'Ce dépôt est annulé.']);
            }

            if ($montant < 1 || $montant > (int) $depot->reste_a_payer) {
                throw ValidationException::withMessages([
                    'montant_paye_ajout' => 'Le montant doit être entre 1 et ' . number_format((int) $depot->reste_a_payer, 0, ',', ' ') . ' FCFA.',
                ]);
            }

            $paiement = Paiement::create([
                'depot_id'      => $depot->id,
                'user_id'       => $user->id,
                'montant'       => $montant,
                'mode'          => $mode,
                'reference'     => $reference,
                'date_paiement' => now(),
            ]);

            $paye = (int) $depot->paiements()->sum('montant');
            $depot->update([
                'montant_paye'  => $paye,
                'reste_a_payer' => (int) $depot->montant_total - $paye,
            ]);

            return $paiement;
        });
    }

    public function changerStatut(Depot $depot, string $statut, User $user, bool $derogation = false, ?string $note = null): Depot
    {
        return DB::transaction(function () use ($depot, $statut, $user, $derogation, $note) {
            $depot = Depot::lockForUpdate()->findOrFail($depot->id);

            if ($statut === $depot->statut) {
                return $depot;
            }

            if (! in_array($statut, self::TRANSITIONS[$depot->statut], true)) {
                throw ValidationException::withMessages(['statut' => "Passage impossible de « {$depot->statut} » à « {$statut} »."]);
            }

            $gestionnaire = in_array($user->role, ['admin', 'gerant'], true);

            if ($statut === 'annule' && ! $gestionnaire) {
                throw ValidationException::withMessages(['statut' => 'Seul un gérant peut annuler un dépôt.']);
            }

            if ($depot->statut === 'pret' && $statut === 'en_traitement' && ! $gestionnaire) {
                throw ValidationException::withMessages(['statut' => 'Seul un gérant peut rouvrir un dépôt déjà prêt.']);
            }

            if ($statut === 'pret' && app(ConsommationService::class)->requise($depot)) {
                throw ValidationException::withMessages(['statut' => "Déclarez d'abord les produits utilisés pour ce dépôt."]);
            }

            if ($statut === 'retire') {
                if (! in_array($user->role, self::ROLES_CAISSE, true)) {
                    throw ValidationException::withMessages(['statut' => 'Un opérateur ne peut pas remettre un dépôt au client.']);
                }
                if ((int) $depot->reste_a_payer > 0 && ! ($gestionnaire && $derogation)) {
                    throw ValidationException::withMessages([
                        'statut' => 'Remise impossible : il reste ' . number_format((int) $depot->reste_a_payer, 0, ',', ' ') . ' FCFA à payer.',
                    ]);
                }
            }

            $depot->update(['statut' => $statut] + ($statut === 'retire' ? ['date_retrait_reelle' => now()] : []));

            match ($statut) {
                'en_traitement' => $depot->articles()->whereIn('statut', ['depose', 'pret'])->update(['statut' => 'en_traitement']),
                'pret'          => $depot->articles()->whereIn('statut', ['depose', 'en_traitement'])->update(['statut' => 'pret']),
                'retire'        => $depot->articles()->where('statut', 'pret')->update(['statut' => 'retire']),
                default         => null,
            };

            $this->historiser($depot, $statut, $user, $note ?? ($statut === 'retire' && (int) $depot->reste_a_payer > 0 ? 'Remis avec dérogation (solde restant)' : null));

            return $depot;
        });
    }

    /** Un article est signalé perdu ou abîmé (l'opérateur doit décrire le problème). */
    public function signaler(Depot $depot, ArticleDepot $article, string $statut, string $note, User $user): void
    {
        abort_unless((int) $article->depot_id === (int) $depot->id, 404);

        if (! in_array($statut, ['perdu', 'abime'], true)) {
            throw ValidationException::withMessages(['statut' => 'Statut invalide.']);
        }

        if (in_array($depot->statut, ['retire', 'annule'], true)) {
            throw ValidationException::withMessages(['statut' => 'Ce dépôt est terminé : impossible de signaler un article.']);
        }

        DB::transaction(function () use ($depot, $article, $statut, $note, $user) {
            $article->update(['statut' => $statut, 'observation' => $note]);

            $this->historiser(
                $depot,
                $depot->statut,
                $user,
                'Article « ' . ($article->typeArticle?->nom ?? '?') . ' » signalé ' . ($statut === 'perdu' ? 'perdu' : 'abîmé') . ' : ' . $note
            );
        });
    }

    public function historiser(Depot $depot, string $statut, ?User $user, ?string $note = null): void
    {
        DepotHistorique::create([
            'depot_id'   => $depot->id,
            'user_id'    => $user?->id,
            'statut'     => $statut,
            'note'       => $note,
            'created_at' => now(),
        ]);
    }

    private function prochainNumero(int $pressingId): string
    {
        $prefix  = 'TK-' . now()->format('Ymd') . '-';
        $dernier = Depot::withTrashed()
            ->where('pressing_id', $pressingId)
            ->where('numero_ticket', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('numero_ticket');

        $n = $dernier ? ((int) substr($dernier, -4)) + 1 : 1;

        return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
