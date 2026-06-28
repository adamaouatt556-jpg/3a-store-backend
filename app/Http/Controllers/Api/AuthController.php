<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Inscription
    // Inscription
    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => 'sometimes|in:gerant,vendeur',
            'telephone'             => 'nullable|string|max:20',
            'photo'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
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
            'role'      => $request->role ?? 'vendeur',
            'telephone' => $request->telephone,
            'photo'     => $photoPath,
            'actif'     => true,
            'valide'    => false,
        ]);

        $token = $user->createToken('3A_STORE')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    // Connexion
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $user  = Auth::user();
        $token = $user->createToken('3A_STORE')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
            'must_change_password' => $user->must_change_password,
        ]);
    }

    // Profil connecté
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Déconnexion
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

        // Changer le mot de passe (utilisateur connecté)
    public function changePassword(Request $request)
    {
        $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $user = auth()->user();

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
            'temp_password'        => null,
        ]);

        return response()->json([
            'message' => 'Mot de passe changé avec succès.',
        ]);
    }
}