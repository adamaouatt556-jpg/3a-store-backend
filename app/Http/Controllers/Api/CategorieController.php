<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategorieController extends Controller
{
    // Liste toutes les catégories
    public function index(Request $request)
    {
        $categories = Categorie::with('boutique')
            ->when($request->boutique_id, function ($q, $id) {
                $q->where('boutique_id', $id);
            })
            ->get();

        return response()->json($categories);
    }

    // Créer une catégorie
    public function store(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'icone'       => 'nullable|string',
            'boutique_id' => 'required|exists:boutiques,id',
        ]);

        $categorie = Categorie::create([
            'nom'         => $request->nom,
            'slug'        => Str::slug($request->nom),
            'description' => $request->description,
            'icone'       => $request->icone,
            'boutique_id' => $request->boutique_id,
        ]);

        return response()->json($categorie, 201);
    }

    // Afficher une catégorie
    public function show(Categorie $categorie)
    {
        return response()->json(
            $categorie->load(['boutique', 'produits'])
        );
    }

    // Modifier une catégorie
    public function update(Request $request, Categorie $categorie)
    {
        $request->validate([
            'nom'         => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icone'       => 'nullable|string',
            'actif'       => 'sometimes|boolean',
        ]);

        if ($request->has('nom')) {
            $request->merge(['slug' => Str::slug($request->nom)]);
        }

        $categorie->update($request->all());

        return response()->json($categorie);
    }

    // Supprimer une catégorie
    public function destroy(Categorie $categorie)
    {
        $categorie->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès.'
        ]);
    }

    // Importer des catégories depuis un fichier CSV
    public function import(Request $request)
    {
        $request->validate([
            'fichier'     => 'required|file|mimes:csv,txt',
            'boutique_id' => 'required|exists:boutiques,id',
        ]);

        $fichier   = $request->file('fichier');
        $lignes    = array_map('str_getcsv', file($fichier->getPathname()));
        $importees = 0;
        $erreurs   = [];

        // Ignorer la première ligne (en-têtes)
        array_shift($lignes);

        foreach ($lignes as $index => $ligne) {
            if (empty($ligne[0])) continue;

            try {
                Categorie::firstOrCreate(
                    [
                        'slug'        => Str::slug($ligne[0]),
                        'boutique_id' => $request->boutique_id,
                    ],
                    [
                        'nom'         => $ligne[0],
                        'description' => $ligne[1] ?? null,
                        'icone'       => $ligne[2] ?? null,
                        'boutique_id' => $request->boutique_id,
                    ]
                );
                $importees++;
            } catch (\Exception $e) {
                $erreurs[] = "Ligne " . ($index + 2) . " : " . $e->getMessage();
            }
        }

        return response()->json([
            'message'  => "$importees catégorie(s) importée(s) avec succès.",
            'erreurs'  => $erreurs,
        ]);
    }
}