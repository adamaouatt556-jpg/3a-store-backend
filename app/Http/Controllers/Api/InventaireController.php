<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\Produit;
use Illuminate\Http\Request;

class InventaireController extends Controller
{
    // Liste tous les inventaires
    public function index(Request $request)
    {
        $inventaires = Inventaire::with(['boutique', 'user'])
            ->when($request->boutique_id, function ($q, $id) {
                $q->where('boutique_id', $id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inv) {
                $inv->nb_lignes = $inv->lignes()->count();
                $inv->nb_ecarts = $inv->lignes()->where('ecart', '!=', 0)->count();
                return $inv;
            });

        return response()->json($inventaires);
    }

    // Créer un nouvel inventaire
    public function store(Request $request)
    {
        $request->validate([
            'boutique_id' => 'required|exists:boutiques,id',
            'note'        => 'nullable|string',
        ]);

        // Vérifier qu'il n'y a pas d'inventaire en cours
        $enCours = Inventaire::where('boutique_id', $request->boutique_id)
            ->where('statut', 'en_cours')
            ->first();

        if ($enCours) {
            return response()->json([
                'message'    => 'Un inventaire est déjà en cours pour cette boutique.',
                'inventaire' => $enCours,
            ], 422);
        }

        // Créer l'inventaire
        $inventaire = Inventaire::create([
            'boutique_id' => $request->boutique_id,
            'user_id'     => auth()->id(),
            'statut'      => 'en_cours',
            'note'        => $request->note,
        ]);

        // Charger tous les produits de la boutique
        $produits = Produit::with('stock')
            ->where('boutique_id', $request->boutique_id)
            ->where('actif', true)
            ->get();

        // Créer une ligne par produit
        foreach ($produits as $produit) {
            $stockSysteme = $produit->stock?->quantite ?? 0;
            InventaireLigne::create([
                'inventaire_id' => $inventaire->id,
                'produit_id'    => $produit->id,
                'stock_systeme' => $stockSysteme,
                'stock_reel'    => $stockSysteme,
                'ecart'         => 0,
            ]);
        }

        return response()->json(
            $inventaire->load(['lignes.produit', 'boutique', 'user']),
            201
        );
    }

    // Afficher un inventaire
    public function show(Inventaire $inventaire)
    {
        return response()->json(
            $inventaire->load(['lignes.produit.categorie', 'boutique', 'user'])
        );
    }

    // Mettre à jour une ligne d'inventaire
    public function updateLigne(Request $request, Inventaire $inventaire, InventaireLigne $ligne)
    {
        $request->validate([
            'stock_reel' => 'required|integer|min:0',
            'note'       => 'nullable|string',
        ]);

        $ligne->update([
            'stock_reel' => $request->stock_reel,
            'ecart'      => $request->stock_reel - $ligne->stock_systeme,
            'note'       => $request->note,
        ]);

        return response()->json($ligne);
    }

    // Valider l'inventaire
    public function valider(Inventaire $inventaire)
    {
        if ($inventaire->statut !== 'en_cours') {
            return response()->json([
                'message' => 'Cet inventaire ne peut pas être validé.'
            ], 422);
        }

        $inventaire->load('lignes');
        $inventaire->valider();

        return response()->json([
            'message'    => 'Inventaire validé avec succès.',
            'inventaire' => $inventaire,
        ]);
    }

    // Annuler l'inventaire
    public function annuler(Inventaire $inventaire)
    {
        if ($inventaire->statut !== 'en_cours') {
            return response()->json([
                'message' => 'Cet inventaire ne peut pas être annulé.'
            ], 422);
        }

        $inventaire->update(['statut' => 'annule']);

        return response()->json([
            'message' => 'Inventaire annulé.'
        ]);
    }

    public function update(Request $request, Inventaire $inventaire) {}

    public function destroy(Inventaire $inventaire)
    {
        $inventaire->delete();
        return response()->json(['message' => 'Inventaire supprimé.']);
    }
}