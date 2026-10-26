# PressManager — notes de version

## Installation / mise à jour
```bash
composer install
cp .env.example .env              # première installation seulement ; configurez la base de données
php artisan key:generate          # première installation seulement
php artisan migrate               # SANS "fresh" : conserve vos données
php artisan db:seed               # facultatif : ajoute les produits et quantités standard de démonstration
php artisan optimize:clear
npm install && npm run build
php artisan serve
```
Comptes de démonstration (mot de passe `password`, à changer avant la mise en ligne) :
`admin@pressing.test`, `gerant@pressing.test`, `caissier@pressing.test`, `operateur@pressing.test`.

Si votre dossier `database/migrations` contient deux migrations « mode_paiement » pour la table `depenses`,
gardez-en une seule (celle fournie ici ne plante pas si la colonne existe déjà).

## Nouveautés de cette version

### Workflow des dépôts par boutons (plus de menu déroulant de statut)
`Déposé → [Commencer] → En traitement → [Marquer prêt] → Prêt → [Remettre au client] → Retiré`
- Chaque changement est enregistré dans un **historique** (qui, quand, note), affiché sur la fiche du dépôt.
- La remise au client exige un solde à zéro, sauf dérogation d'un gérant/admin.
- « Rouvrir » (gérant/admin) ramène un dépôt prêt en traitement. « Annuler » : gérant/admin.
- Un opérateur ou un caissier peut **signaler un article perdu ou abîmé** (description obligatoire).

### Rôles
| Action | Caissier | Opérateur | Gérant / Admin |
|---|---|---|---|
| Créer un dépôt, imprimer le ticket, encaisser | oui | non | oui |
| Commencer le traitement, marquer prêt, déclarer les produits | oui | oui | oui |
| Remettre au client | oui | non | oui |
| Voir les montants, les clients | oui | non | oui |
| Annuler, rouvrir, dérogation, quantités standard, achats, dépenses, rapports | non | non | oui |
L'opérateur dispose d'une page **« À traiter »** (sans aucun montant) et d'un tableau de bord adapté.

### Produits consommés
- **Quantités standard** (menu « Quantités standard ») : pour chaque service, les produits consommés par article
  (ex. Lavage → 0,05 L de lessive par article).
- Quand on marque « prêt » un dépôt qui consomme des produits, une page **« Produits utilisés »** s'affiche avec les
  quantités pré-remplies ; on corrige si besoin (ex. 0,5 L) puis on valide : le stock diminue (mouvements de sortie liés au dépôt).
- Déclaration possible **une seule fois** par dépôt ; refus si le stock est insuffisant.
- Le coût des produits et la marge de chaque dépôt sont visibles sur la fiche (gérant/admin).
- Les stocks sont comptés dans la plus petite unité (L, kg, pièce) avec 3 décimales.
- **Conditionnement** : un produit peut avoir une contenance (ex. Bidon = 12 L) ; à l'achat, on peut saisir
  « 2 bidons » et le stock est converti en litres.

### Reçus
- **Ticket de dépôt** imprimable (format ticket de caisse 80 mm) : bouton « Ticket » sur la fiche du dépôt.
- **Reçu de paiement** pour chaque encaissement (lien « Reçu » dans la liste des paiements).
- Bouton **WhatsApp** : ouvre la conversation avec le client et un message pré-rempli (aucun service payant).
- Les conditions de garde imprimées sur le ticket (30 jours) sont à adapter dans `resources/views/recus/ticket.blade.php`.

### Interface
Page de connexion, tableau de bord et formulaire de dépôt redessinés.

## Historique des corrections précédentes
- `sous_total` inexistant retiré ; statut `en_cours` → `en_traitement` ; modes de paiement alignés sur l'enum.
- Isolation par pressing (trait `BelongsToPressing`), authentification, rôles.
- Prix lus depuis la grille tarifaire côté serveur ; paiement ≤ reste à payer ; transactions partout.
- Stock jamais négatif ; achat = mouvements d'entrée + dépense liée ; rapports sans double déduction des achats.

## Non couvert (à faire)
Gestion du catalogue (services, types d'articles, tarifs) et des utilisateurs par écrans, clôture de caisse,
envoi automatique de SMS, tests automatisés.
Le code a été vérifié par analyse de syntaxe PHP et contrôle des routes, mais pas exécuté dans un navigateur :
testez chaque écran après `php artisan migrate`.
