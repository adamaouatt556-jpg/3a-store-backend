<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Forfait;
use Illuminate\Http\Request;

class ForfaitController extends Controller
{
    // Liste tous les forfaits
    public function index()
    {
        $forfaits = Forfait::where('actif', true)->get();
        return response()->json($forfaits);
    }

    // Créer un forfait (Super Admin uniquement)
    public function store(Request $request)
    {
        $request->validate([
            'nom'                      => 'required|string',
            'type'                     => 'required|in:mensuel,trimestriel,semestriel,annuel',
            'prix'                     => 'required|numeric|min:0',
            'nb_boutiques'             => 'required|integer|min:-1',
            'nb_vendeurs_par_boutique' => 'required|integer|min:1',
            'duree_jours'              => 'required|integer|min:1',
            'description'              => 'nullable|string',
        ]);

        $forfait = Forfait::create($request->all());
        return response()->json($forfait, 201);
    }

    // Afficher un forfait
    public function show(Forfait $forfait)
    {
        return response()->json($forfait->load('abonnements.user'));
    }

    // Modifier un forfait
    public function update(Request $request, Forfait $forfait)
    {
        $request->validate([
            'nom'         => 'sometimes|string',
            'prix'        => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'actif'       => 'sometimes|boolean',
        ]);

        $forfait->update($request->all());
        return response()->json($forfait);
    }

    // Supprimer un forfait
    public function destroy(Forfait $forfait)
    {
        $forfait->delete();
        return response()->json(['message' => 'Forfait supprimé.']);
    }
}