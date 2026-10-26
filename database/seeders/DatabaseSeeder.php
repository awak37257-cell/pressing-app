<?php

namespace Database\Seeders;

use App\Models\CategorieDepense;
use App\Models\CategorieProduit;
use App\Models\Pressing;
use App\Models\Produit;
use App\Models\Service;
use App\Models\ServiceProduit;
use App\Models\Tarif;
use App\Models\TypeArticle;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $pressing = Pressing::firstOrCreate(['nom' => 'Pressing Démo'], ['adresse' => 'Abidjan']);
        $pid = $pressing->id;

        // Comptes de démonstration : à supprimer ou changer avant toute mise en production
        foreach (['admin', 'gerant', 'caissier', 'operateur'] as $role) {
            User::updateOrCreate(
                ['email' => "$role@pressing.test"],
                ['pressing_id' => $pid, 'nom' => ucfirst($role), 'role' => $role, 'password' => 'password', 'actif' => true]
            );
        }

        $categories = [];
        foreach (['Détergent', 'Solvant', 'Assouplissant', 'Cintres et housses'] as $nom) {
            $categories[$nom] = CategorieProduit::firstOrCreate(['pressing_id' => $pid, 'nom' => $nom]);
        }

        foreach (['Électricité', 'Eau', 'Salaires', 'Loyer', 'Achats de produits', 'Remboursement client'] as $nom) {
            CategorieDepense::firstOrCreate(['pressing_id' => $pid, 'nom' => $nom]);
        }

        // Prix de démonstration (FCFA) : à remplacer par vos vrais tarifs
        $services = ['Lavage' => 1000, 'Repassage' => 500, 'Nettoyage à sec' => 2000, 'Détachage' => 300];
        $types    = ['Chemise' => 1, 'Pantalon' => 1, 'Costume' => 3, 'Robe' => 2, 'Couette' => 5];

        $serviceModels = [];
        foreach ($types as $typeNom => $coef) {
            $type = TypeArticle::firstOrCreate(['pressing_id' => $pid, 'nom' => $typeNom]);

            foreach ($services as $serviceNom => $base) {
                $service = $serviceModels[$serviceNom] ??= Service::firstOrCreate(['pressing_id' => $pid, 'nom' => $serviceNom]);

                Tarif::updateOrCreate(
                    ['type_article_id' => $type->id, 'service_id' => $service->id],
                    ['prix' => $base * $coef, 'prix_express' => (int) round($base * $coef / 2)]
                );
            }
        }

        // Produits de démonstration (le stock est enregistré par un mouvement d'entrée)
        $gerant = User::where('email', 'gerant@pressing.test')->first();
        $stock  = app(StockService::class);

        $produits = [];
        $definitions = [
            // nom, catégorie, unité, contenance, libellé, seuil, prix d'achat par unité, stock initial
            ['Lessive liquide', 'Détergent', 'L', 12, 'Bidon', 5, 2500, 24],
            ['Solvant de nettoyage à sec', 'Solvant', 'L', 20, 'Bidon', 5, 3500, 40],
            ['Cintres', 'Cintres et housses', 'piece', null, null, 50, 150, 200],
            ['Housses plastique', 'Cintres et housses', 'piece', null, null, 50, 100, 200],
        ];

        foreach ($definitions as [$nom, $cat, $unite, $contenance, $libelle, $seuil, $prix, $initial]) {
            $produit = Produit::firstOrCreate(
                ['pressing_id' => $pid, 'nom' => $nom],
                [
                    'categorie_produit_id'    => $categories[$cat]->id,
                    'unite'                   => $unite,
                    'contenance'              => $contenance,
                    'libelle_conditionnement' => $libelle,
                    'stock_actuel'            => 0,
                    'seuil_alerte'            => $seuil,
                    'prix_achat_moyen'        => $prix,
                ]
            );

            if ($produit->wasRecentlyCreated && $gerant) {
                $stock->mouvement($produit, 'entree', $initial, 'Stock initial (démo)', $gerant);
            }

            $produits[$nom] = $produit;
        }

        // Quantités standard (démo) : à ajuster selon vos machines et vos habitudes
        $standards = [
            ['Lavage', 'Lessive liquide', 0.05],
            ['Repassage', 'Cintres', 1],
            ['Nettoyage à sec', 'Solvant de nettoyage à sec', 0.1],
            ['Nettoyage à sec', 'Housses plastique', 1],
        ];

        foreach ($standards as [$serviceNom, $produitNom, $quantite]) {
            ServiceProduit::updateOrCreate(
                [
                    'service_id'      => $serviceModels[$serviceNom]->id,
                    'type_article_id' => null,
                    'produit_id'      => $produits[$produitNom]->id,
                ],
                ['quantite_par_article' => $quantite]
            );
        }
    }
}
