<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'slug',
        'description',
        'icone',
        'boutique_id',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    // Générer le slug automatiquement
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categorie) {
            $categorie->slug = Str::slug($categorie->nom);
        });
    }

    // Une catégorie appartient à une boutique
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    // Une catégorie a plusieurs produits
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }
}