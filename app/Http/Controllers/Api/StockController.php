<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Produit;
use Illuminate\Http\Request;

class StockController extends Controller
{
    // Liste tous les stocks
    public function index(Request $request)
    {
        $stocks = Stock::with(['produit.categorie', 'produit.boutique'])
            ->when($request->boutique_id, function ($q, $id) {
                $q->whereHas('produit', function ($q) use ($id) {
                    $q->where('boutique_id', $id);
                });
            })
            ->get();

        return response()->json($stocks);
    }

    // Afficher le stock d'un produit
    public function show(Produit $produit)
    {
        $stock = Stock::with('produit')
            ->where('produit_id', $produit->id)
            ->firstOrFail();

        return response()->json($stock);
    }

    // Produits en alerte de stock bas ou rupture
    public function alertes(Request $request)
    {
        $produits = Produit::with(['stock', 'categorie', 'boutique'])
            ->when($request->boutique_id, function ($q, $id) {
                $q->where('boutique_id', $id);
            })
            ->whereHas('stock', function ($q) {
                $q->whereColumn('quantite', '<=', 
                    \DB::raw('stock_alerte')
                );
            })
            ->get()
            ->map(function ($produit) {
                $produit->statut = $produit->stock->quantite === 0
                    ? 'rupture'
                    : 'stock_bas';
                return $produit;
            });

        return response()->json($produits);
    }
}