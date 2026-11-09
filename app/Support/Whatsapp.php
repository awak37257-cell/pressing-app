<?php

namespace App\Support;

use App\Models\ArticleDepot;

class Whatsapp
{
    /**
     * Lien wa.me avec message pré-rempli (aucun service d'envoi payant nécessaire).
     * Numéros ivoiriens : 10 chiffres (01, 05, 07…) → l'indicatif 225 est ajouté s'il manque.
     */
    public static function url(?string $telephone, string $message): ?string
    {
        $chiffres = preg_replace('/\D+/', '', (string) $telephone);

        if ($chiffres === '') {
            return null;
        }

        if (str_starts_with($chiffres, '00')) {
            $chiffres = substr($chiffres, 2);
        }

        if (! str_starts_with($chiffres, '225')) {
            $chiffres = '225'.$chiffres;
        }

        return 'https://wa.me/'.$chiffres.'?text='.rawurlencode($message);
    }

    /**
     * @param  iterable<ArticleDepot>  $articles
     */
    public static function articleIncidentNotice(iterable $articles): string
    {
        $incidents = collect($articles)
            ->filter(fn (ArticleDepot $article): bool => in_array($article->statut, ['perdu', 'abime'], true))
            ->map(function (ArticleDepot $article): string {
                $designation = ($article->quantite > 1 ? $article->quantite.' × ' : '')
                    .($article->typeArticle?->nom ?? 'Article');
                $detail = $designation.' — '.($article->statut === 'perdu' ? 'perdu' : 'abîmé');
                $observation = trim((string) $article->observation);

                if ($observation !== '') {
                    $detail .= ' : '.$observation;
                }

                return $detail;
            });

        if ($incidents->isEmpty()) {
            return '';
        }

        $introduction = $incidents->count() === 1
            ? 'Information importante : article signalé'
            : 'Informations importantes : articles signalés';

        return "\n\n".$introduction.' : '.$incidents->implode('; ').'.';
    }
}
