<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Liste tous les utilisateurs
    public function index(Request $request)
    {
        $user  = auth()->user();

        $users = User::with(['forfaitActif', 'boutiques', 'boutiquesAssignees'])
            ->when($request->role, function ($q, $role) {
                $q->where('role', $role);
            })
            ->when($user->isGerant(), function ($q) use ($user) {
                $q->whereHas('boutiquesAssignees', function ($q) use ($user) {
                    $q->where('gerant_id', $user->id);
                })->where('role', 'vendeur');
            })
            ->when($user->isVendeur(), function ($q) use ($user) {
                $q->where('id', $user->id);
            })
            ->get()
            ->map(function ($u) {
                $u->boutiques_assignees = $u->boutiquesAssignees;
                return $u;
            });

        return response()->json($users);
    }

    // Créer un utilisateur
    public function store(Request $request)
{
    $request->validate([
        'name'        => 'required|string|max:255',
        'email'       => 'required|email|unique:users,email',
        'password'    => 'required|string|min:8',
        'role'        => 'required|in:gerant,vendeur',
        'telephone'   => 'nullable|string|max:20',
        'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'boutique_id' => 'nullable|exists:boutiques,id',
    ]);

    // Upload photo
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('users', 'public');
    }

    $user = User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'password'  => Hash::make($request->password),
        'role'      => $request->role,
        'telephone' => $request->telephone,
        'photo'     => $photoPath,
        'actif'     => true,
        'valide'    => $request->role === 'vendeur' ? true : false,
    ]);

    // Assigner automatiquement à la boutique si vendeur
    if ($request->role === 'vendeur' && $request->boutique_id) {
        $boutique = \App\Models\Boutique::findOrFail($request->boutique_id);

        // Vérifier limite vendeurs
        if (!$boutique->peutAjouterVendeur()) {
            return response()->json([
                'message' => 'Limite de vendeurs atteinte pour cette boutique.'
            ], 422);
        }

        $user->boutiquesAssignees()->attach($request->boutique_id, [
            'role'  => 'vendeur',
            'actif' => true,
        ]);
    }

    return response()->json($user, 201);
}

    // Afficher un utilisateur
    public function show(User $user)
    {
        return response()->json(
            $user->load(['forfaitActif', 'boutiques', 'abonnements.forfait'])
        );
    }

    // Modifier un utilisateur
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'actif'     => 'sometimes|boolean',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Upload nouvelle photo
        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('users', 'public');
        }

        $user->update($request->except('photo') +
            ($request->hasFile('photo') ? ['photo' => $user->photo] : [])
        );

        return response()->json($user);
    }

    // Supprimer un utilisateur
    public function destroy(User $user)
    {
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    // Assigner un vendeur à une boutique
    public function assignerBoutique(Request $request, User $user)
    {
        $request->validate([
            'boutique_id' => 'required|exists:boutiques,id',
        ]);

        $boutique = Boutique::findOrFail($request->boutique_id);

        // Vérifier limite vendeurs
        if (!$boutique->peutAjouterVendeur()) {
            return response()->json([
                'message' => 'Limite de vendeurs atteinte pour cette boutique.'
            ], 422);
        }

        // Assigner
        $user->boutiquesAssignees()->syncWithoutDetaching([
            $request->boutique_id => ['role' => 'vendeur', 'actif' => true]
        ]);

        return response()->json([
            'message' => 'Vendeur assigné avec succès.'
        ]);
    }

    // Retirer un vendeur d'une boutique
    public function retirerBoutique(Request $request, User $user)
    {
        $request->validate([
            'boutique_id' => 'required|exists:boutiques,id',
        ]);

        $user->boutiquesAssignees()->detach($request->boutique_id);

        return response()->json([
            'message' => 'Vendeur retiré de la boutique.'
        ]);
    }

        // Réinitialiser le mot de passe (Super Admin)
    public function resetPassword(Request $request, User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Action non autorisée.'
            ], 403);
        }

        $request->validate([
            'temp_password' => 'required|string|min:6',
        ]);

        $user->update([
            'password'             => Hash::make($request->temp_password),
            'must_change_password' => true,
            'temp_password'        => $request->temp_password,
        ]);

        return response()->json([
            'message' => 'Mot de passe réinitialisé. L\'utilisateur devra le changer à sa prochaine connexion.',
        ]);
    }
}