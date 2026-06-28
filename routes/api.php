<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoutiqueController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\MouvementStockController;
use App\Http\Controllers\Api\ForfaitController;
use App\Http\Controllers\Api\AbonnementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventaireController;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Forfaits publics (visibles sans connexion)
Route::get('/forfaits', [ForfaitController::class, 'index']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Boutiques
    Route::apiResource('boutiques', BoutiqueController::class);

    // Catégories
    Route::apiResource('categories', CategorieController::class);
    Route::post('categories/import', [CategorieController::class, 'import']);

    // Produits
    Route::apiResource('produits', ProduitController::class);

    // Stocks
    Route::get('stocks',             [StockController::class, 'index']);
    Route::get('stocks/alertes',     [StockController::class, 'alertes']);
    Route::get('stocks/{produit}',   [StockController::class, 'show']);

    // Mouvements
    Route::apiResource('mouvements', MouvementStockController::class);

    // Forfaits (CRUD — Super Admin)
    Route::apiResource('forfaits', ForfaitController::class)->except('index');

    // Abonnements
    Route::apiResource('abonnements', AbonnementController::class);
    Route::post('abonnements/{abonnement}/valider',   [AbonnementController::class, 'valider']);
    Route::post('abonnements/{abonnement}/suspendre', [AbonnementController::class, 'suspendre']);

    // Utilisateurs
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/assigner-boutique', [UserController::class, 'assignerBoutique']);
    Route::post('users/{user}/retirer-boutique',  [UserController::class, 'retirerBoutique']);

    // Inventaires
    Route::get('inventaires',                                    [InventaireController::class, 'index']);
    Route::post('inventaires',                                   [InventaireController::class, 'store']);
    Route::get('inventaires/{inventaire}',                       [InventaireController::class, 'show']);
    Route::delete('inventaires/{inventaire}',                    [InventaireController::class, 'destroy']);
    Route::post('inventaires/{inventaire}/valider',              [InventaireController::class, 'valider']);
    Route::post('inventaires/{inventaire}/annuler',              [InventaireController::class, 'annuler']);
    Route::put('inventaires/{inventaire}/lignes/{ligne}',        [InventaireController::class, 'updateLigne']);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);

});