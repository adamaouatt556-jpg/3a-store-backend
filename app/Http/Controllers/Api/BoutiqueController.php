<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BoutiqueController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $boutiques = Boutique::with(['gerant'])
            ->when($user->isVendeur(), function ($q) use ($user) {
                $q->whereHas('membres', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->when($user->isGerant(), function ($q) use ($user) {
                $q->where('gerant_id', $user->id);
            })
            ->get();

        return response()->json($boutiques);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'adresse'   => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'email'     => 'nullable|email',
        ]);

        // Vérifier si le gérant peut créer une boutique
        $user = auth()->user();
        if ($user->isGerant() && !$user->peutCreerBoutique()) {
            return response()->json([
            'message' => 'Limite de boutiques atteinte pour votre forfait.'
        ], 422);
}

        $boutique = Boutique::create([
            'nom'       => $request->nom,
            'slug'      => Str::slug($request->nom),
            'adresse'   => $request->adresse,
            'telephone' => $request->telephone,
            'email'     => $request->email,
            'gerant_id' => auth()->id(),
        ]);

        return response()->json($boutique, 201);
    }

    public function show(Boutique $boutique)
    {
        return response()->json(
            $boutique->load(['gerant', 'categories', 'produits', 'vendeurs'])
        );
    }

    public function update(Request $request, Boutique $boutique)
    {
        $request->validate([
            'nom'       => 'sometimes|string|max:255',
            'adresse'   => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'email'     => 'nullable|email',
            'actif'     => 'sometimes|boolean',
        ]);

        $boutique->update($request->all());
        return response()->json($boutique);
    }

    public function destroy(Boutique $boutique)
    {
        $boutique->delete();
        return response()->json(['message' => 'Boutique supprimée avec succès.']);
    }
}