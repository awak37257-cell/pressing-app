<?php

use App\Http\Controllers\AchatController;
use App\Http\Controllers\Auth\ForcedPasswordController;
use App\Http\Controllers\CategorieProduitController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClotureCaisseController;
use App\Http\Controllers\ConsommationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DepotActionController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MouvementStockController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PressingsController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\RecuController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceProduitController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\TraitementController;
use App\Http\Controllers\TypeArticleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'pressing'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Profil (tous les rôles)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/caisse/cloture', [ClotureCaisseController::class, 'index'])->name('caisse.cloture');
    Route::post('/caisse/cloture', [ClotureCaisseController::class, 'store'])->name('caisse.cloture.store');
    // ---- Dépôts
    Route::get('depots/alertes/prets', [DepotController::class, 'alertesPrets'])
        ->middleware('role:admin,gerant,caissier')
        ->name('depots.alertes-prets');
    Route::get('depots/prets', [DepotController::class, 'prets'])
        ->middleware('role:admin,gerant,caissier')
        ->name('depots.prets');
    Route::resource('depots', DepotController::class);

    // Workflow par boutons avec rôles stricts
    Route::get('traitement', [TraitementController::class, 'index'])->name('traitement.index');

    Route::post('depots/{depot}/commencer', [DepotActionController::class, 'commencer'])
        ->middleware('role:admin,gerant,operateur')
        ->name('depots.commencer');

    Route::post('depots/{depot}/pret', [DepotActionController::class, 'pret'])
        ->middleware('role:admin,gerant,operateur')
        ->name('depots.pret');

    Route::post('depots/{depot}/retirer', [DepotActionController::class, 'retirer'])
        ->middleware('role:admin,gerant,caissier')
        ->name('depots.retirer');

    Route::post('depots/{depot}/encaisser-depuis-prets', [DepotController::class, 'encaisserDepuisPrets'])
        ->middleware('role:admin,gerant,caissier')
        ->name('depots.prets.encaisser');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('employes', EmployeController::class)->except(['show']);
    });
    Route::post('depots/{depot}/rouvrir', [DepotActionController::class, 'rouvrir'])
        ->middleware('role:admin,gerant')
        ->name('depots.rouvrir');

    Route::post('depots/{depot}/articles/{article}/signaler', [DepotActionController::class, 'signaler'])
        ->middleware('role:admin,gerant,operateur')
        ->name('depots.signaler');

    // Produits utilisés pour un dépôt
    Route::get('depots/{depot}/consommation', [ConsommationController::class, 'create'])
        ->middleware('role:admin,gerant,operateur')
        ->name('consommations.create');
    Route::post('depots/{depot}/consommation', [ConsommationController::class, 'store'])
        ->middleware('role:admin,gerant,operateur')
        ->name('consommations.store');

    // Reçus imprimables
    Route::get('depots/{depot}/ticket', [RecuController::class, 'ticket'])
        ->middleware('role:admin,gerant,caissier')->name('depots.ticket');
    Route::get('paiements/{paiement}/recu', [RecuController::class, 'paiement'])
        ->middleware('role:admin,gerant,caissier')->name('paiements.recu');

    // ---- Modification de paiement (admin + gérant uniquement)
    Route::get('paiements/{paiement}/edit', [PaiementController::class, 'edit'])
        ->middleware('role:admin,gerant')->name('paiements.edit');
    Route::put('paiements/{paiement}', [PaiementController::class, 'update'])
        ->middleware('role:admin,gerant')->name('paiements.update');

    // ---- Clients, produits, stocks
    Route::resource('clients', ClientController::class);

    Route::resource('produits', ProduitController::class);
    Route::resource('categories-produits', CategorieProduitController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['categories-produits' => 'categoriesProduit'])
        ->middleware('role:admin,gerant');
    Route::post('produits/{produit}/ajuster-stock', [ProduitController::class, 'ajusterStock'])
        ->name('produits.ajuster-stock');

    Route::resource('quantites-standard', ServiceProduitController::class)
        ->only(['index'])
        ->parameters(['quantites-standard' => 'standard'])
        ->middleware('role:admin,gerant');

    Route::resource('achats', AchatController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('depenses', DepenseController::class)->except('show');
    Route::resource('fournisseurs', FournisseurController::class)->except('show');

    Route::get('mouvements', [MouvementStockController::class, 'index'])->name('mouvements.index');
    Route::get('rapports', [RapportController::class, 'index'])
        ->middleware('role:admin,gerant')->name('rapports.index');

    // ---- Services & Tarifs (admin + gérant)
    Route::resource('services', ServiceController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('role:admin,gerant');

    Route::resource('types-articles', TypeArticleController::class)
        ->only(['store', 'update', 'destroy'])
        ->parameters(['types-articles' => 'typeArticle'])
        ->middleware('role:admin,gerant');

    Route::post('tarifs', [TarifController::class, 'store'])
        ->middleware('role:admin,gerant')->name('tarifs.store');
    Route::post('tarifs/pre-remplir', [TarifController::class, 'preRemplirPourTests'])
        ->middleware('role:admin,gerant')->name('tarifs.pre-remplir');
    Route::put('tarifs/{tarif}', [TarifController::class, 'update'])
        ->middleware('role:admin,gerant')->name('tarifs.update');
    Route::delete('tarifs/{tarif}', [TarifController::class, 'destroy'])
        ->middleware('role:admin,gerant')->name('tarifs.destroy');

    // ---- Pressings (admin uniquement)
    Route::resource('pressings', PressingsController::class)
        ->except('show')
        ->middleware('role:admin');
    Route::get('/changer-mot-de-passe-force', [ForcedPasswordController::class, 'edit'])->name('password.force.change');
    Route::put('/changer-mot-de-passe-force', [ForcedPasswordController::class, 'update'])->name('password.force.update');
});
