<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    // Liste tous les produits
    public function index(Request $request)
    {
        $produits = Produit::with(['categorie', 'boutique', 'stock'])
            ->when($request->boutique_id, function ($q, $id) {
                $q->where('boutique_id', $id);
            })
            ->when($request->categorie_id, function ($q, $id) {
                $q->where('categorie_id', $id);
            })
            ->when($request->search, function ($q, $s) {
                $q->where('nom', 'ilike', "%$s%")
                  ->orWhere('reference', 'ilike', "%$s%");
            })
            ->paginate(15);

        return response()->json($produits);
    }

    // Créer un produit
    public function store(Request $request)
    {
        $request->validate([
            'reference'    => 'required|string|unique:produits,reference',
            'nom'          => 'required|string|max:200',
            'description'  => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
            'boutique_id'  => 'required|exists:boutiques,id',
            'prix_achat'   => 'required|numeric|min:0',
            'prix_vente'   => 'required|numeric|min:0|gte:prix_achat',
            'stock_alerte' => 'nullable|integer|min:0',
            'unite'        => 'nullable|string',
            'quantite'     => 'required|integer|min:0',
            'images.*'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Gérer les images
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('produits', 'public');
                $images[] = $path;
            }
        }

        // Créer le produit
        $produit = Produit::create([
            'reference'    => $request->reference,
            'nom'          => $request->nom,
            'description'  => $request->description,
            'categorie_id' => $request->categorie_id,
            'boutique_id'  => $request->boutique_id,
            'prix_achat'   => $request->prix_achat,
            'prix_vente'   => $request->prix_vente,
            'stock_alerte' => $request->stock_alerte ?? 5,
            'unite'        => $request->unite ?? 'pièce',
            'images'       => $images,
        ]);

        // Créer le stock initial
        Stock::create([
            'produit_id' => $produit->id,
            'quantite'   => $request->quantite,
        ]);

        return response()->json(
            $produit->load(['categorie', 'stock']),
            201
        );
    }

    // Afficher un produit
    public function show(Produit $produit)
    {
        return response()->json(
            $produit->load(['categorie', 'boutique', 'stock', 'mouvements.user'])
        );
    }

    // Modifier un produit
    public function update(Request $request, Produit $produit)
    {
        $request->validate([
            'nom'          => 'sometimes|string|max:200',
            'description'  => 'nullable|string',
            'categorie_id' => 'sometimes|exists:categories,id',
            'prix_achat'   => 'sometimes|numeric|min:0',
            'prix_vente'   => 'sometimes|numeric|min:0',
            'stock_alerte' => 'nullable|integer|min:0',
            'unite'        => 'nullable|string',
            'actif'        => 'sometimes|boolean',
            'images.*'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Gérer les nouvelles images
        if ($request->hasFile('images')) {
            // Supprimer les anciennes images
            foreach ($produit->images ?? [] as $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('produits', 'public');
                $images[] = $path;
            }
            $request->merge(['images' => $images]);
        }

        $produit->update($request->except('images') + 
            ($request->hasFile('images') ? ['images' => $request->images] : [])
        );

        return response()->json($produit->load(['categorie', 'stock']));
    }

    // Supprimer un produit
    public function destroy(Produit $produit)
    {
        // Supprimer les images
        foreach ($produit->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $produit->delete();

        return response()->json([
            'message' => 'Produit supprimé avec succès.'
        ]);
    }
}