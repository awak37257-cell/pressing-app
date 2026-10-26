<?php

use App\Http\Controllers\AchatController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConsommationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DepotActionController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MouvementStockController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\RecuController;
use App\Http\Controllers\ServiceProduitController;
use App\Http\Controllers\TraitementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'pressing'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Dépôts
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

    Route::post('depots/{depot}/rouvrir', [DepotActionController::class, 'rouvrir'])
        ->middleware('role:admin,gerant')
        ->name('depots.rouvrir');

    Route::post('depots/{depot}/articles/{article}/signaler', [DepotActionController::class, 'signaler'])
        ->middleware('role:admin,gerant,operateur')
        ->name('depots.signaler');

    // Produits utilisés pour un dépôt
    Route::get('depots/{depot}/consommation', [ConsommationController::class, 'create'])->name('consommations.create');
    Route::post('depots/{depot}/consommation', [ConsommationController::class, 'store'])->name('consommations.store');

    // Reçus imprimables
    Route::get('depots/{depot}/ticket', [RecuController::class, 'ticket'])
        ->middleware('role:admin,gerant,caissier')->name('depots.ticket');
    Route::get('paiements/{paiement}/recu', [RecuController::class, 'paiement'])
        ->middleware('role:admin,gerant,caissier')->name('paiements.recu');

    // ---- Clients, produits, stocks
    Route::resource('clients', ClientController::class);

    Route::resource('produits', ProduitController::class);
    Route::post('produits/{produit}/ajuster-stock', [ProduitController::class, 'ajusterStock'])
        ->name('produits.ajuster-stock');

    Route::resource('quantites-standard', ServiceProduitController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['quantites-standard' => 'standard'])
        ->middleware('role:admin,gerant');

    Route::resource('achats', AchatController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('depenses', DepenseController::class)->except('show');
    Route::resource('fournisseurs', FournisseurController::class)->except('show');

    Route::get('mouvements', [MouvementStockController::class, 'index'])->name('mouvements.index');
    Route::get('rapports', [RapportController::class, 'index'])
        ->middleware('role:admin,gerant')->name('rapports.index');
});