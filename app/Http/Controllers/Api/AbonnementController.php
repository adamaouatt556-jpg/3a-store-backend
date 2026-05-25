<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Forfait;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    // Liste tous les abonnements (Super Admin)
    // ou abonnements du gérant connecté
    public function index()
    {
        $user = auth()->user();

        $abonnements = Abonnement::with(['user', 'forfait', 'validePar'])
            ->when(!$user->isSuperAdmin(), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($abonnements);
    }

    // Créer un abonnement (gérant s'abonne)
    public function store(Request $request)
    {
        $request->validate([
            'forfait_id'   => 'required|exists:forfaits,id',
            'montant_paye' => 'required|numeric|min:0',
            'note'         => 'nullable|string',
        ]);

        $abonnement = Abonnement::create([
            'user_id'      => auth()->id(),
            'forfait_id'   => $request->forfait_id,
            'statut'       => 'en_attente',
            'montant_paye' => $request->montant_paye,
            'note'         => $request->note,
        ]);

        return response()->json(
            $abonnement->load(['forfait', 'user']),
            201
        );
    }

    // Afficher un abonnement
    public function show(Abonnement $abonnement)
    {
        return response()->json(
            $abonnement->load(['user', 'forfait', 'validePar'])
        );
    }

    // Valider un abonnement (Super Admin uniquement)
    public function valider(Request $request, Abonnement $abonnement)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Action non autorisée.'
            ], 403);
        }

        if ($abonnement->statut !== 'en_attente') {
            return response()->json([
                'message' => 'Cet abonnement ne peut pas être validé.'
            ], 422);
        }

        $abonnement->valider(auth()->user());

        return response()->json([
            'message'    => 'Abonnement validé avec succès.',
            'abonnement' => $abonnement->load(['user', 'forfait']),
        ]);
    }

    // Suspendre un abonnement (Super Admin)
    public function suspendre(Abonnement $abonnement)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Action non autorisée.'
            ], 403);
        }

        $abonnement->update(['statut' => 'suspendu']);
        $abonnement->user->update(['valide' => false]);

        return response()->json([
            'message' => 'Abonnement suspendu.'
        ]);
    }

    public function update(Request $request, Abonnement $abonnement)
    {
        $request->validate([
            'note' => 'nullable|string',
        ]);

        $abonnement->update($request->only('note'));
        return response()->json($abonnement);
    }

    public function destroy(Abonnement $abonnement)
    {
        $abonnement->delete();
        return response()->json(['message' => 'Abonnement supprimé.']);
    }
}