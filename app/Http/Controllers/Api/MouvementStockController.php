<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Http\Request;

class MouvementStockController extends Controller
{
    // Liste tous les mouvements
    public function index(Request $request)
    {
        $mouvements = MouvementStock::with(['produit', 'user'])
            ->when($request->produit_id, function ($q, $id) {
                $q->where('produit_id', $id);
            })
            ->when($request->type, function ($q, $type) {
                $q->where('type', $type);
            })
            ->when($request->boutique_id, function ($q, $id) {
                $q->whereHas('produit', function ($q) use ($id) {
                    $q->where('boutique_id', $id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($mouvements);
    }

    // Créer un mouvement
    public function store(Request $request)
    {
        $request->validate([
            'produit_id'    => 'required|exists:produits,id',
            'type' => 'required|in:entree,sortie,ajustement,retour,perte',
            'quantite'      => 'required|integer|min:1',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'motif'         => 'nullable|string',
            'note'          => 'nullable|string',
        ]);

        // Vérifier stock suffisant pour une sortie
        if ($request->type === 'sortie') {
            $stock = Stock::where('produit_id', $request->produit_id)
                ->firstOrFail();

            if ($stock->quantite < $request->quantite) {
                return response()->json([
                    'message' => 'Stock insuffisant.',
                    'stock_disponible' => $stock->quantite,
                ], 422);
            }
        }

        $mouvement = MouvementStock::create([
            'produit_id'    => $request->produit_id,
            'user_id'       => auth()->id(),
            'type'          => $request->type,
            'quantite'      => $request->quantite,
            'prix_unitaire' => $request->prix_unitaire,
            'motif'         => $request->motif,
            'note'          => $request->note,
        ]);

        return response()->json(
            $mouvement->load(['produit.stock', 'user']),
            201
        );
    }

    // Afficher un mouvement
    public function show(MouvementStock $mouvement)
    {
        return response()->json(
            $mouvement->load(['produit', 'user'])
        );
    }

    // Modifier un mouvement — uniquement le motif et la note
    public function update(Request $request, MouvementStock $mouvement)
    {
        $request->validate([
            'motif' => 'nullable|string',
            'note'  => 'nullable|string',
        ]);

        $mouvement->update($request->only(['motif', 'note']));

        return response()->json($mouvement);
    }

    // Supprimer un mouvement
    public function destroy(MouvementStock $mouvement)
    {
        $mouvement->delete();

        return response()->json([
            'message' => 'Mouvement supprimé avec succès.'
        ]);
    }
}