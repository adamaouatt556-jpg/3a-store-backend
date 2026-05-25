<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Boutique extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'slug',
        'adresse',
        'telephone',
        'email',
        'logo',
        'gerant_id',
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

        static::creating(function ($boutique) {
            $boutique->slug = Str::slug($boutique->nom);
        });
    }

    // Une boutique appartient à un gérant
    public function gerant()
    {
        return $this->belongsTo(User::class, 'gerant_id');
    }

    // Une boutique a plusieurs catégories
    public function categories()
    {
        return $this->hasMany(Categorie::class);
    }

    // Une boutique a plusieurs produits
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    // Une boutique a plusieurs vendeurs
    public function vendeurs()
    {
        return $this->belongsToMany(User::class, 'boutique_user')
                    ->withPivot('role', 'actif')
                    ->withTimestamps()
                    ->wherePivot('role', 'vendeur');
    }

    // Tous les membres (gérant + vendeurs)
    public function membres()
    {
        return $this->belongsToMany(User::class, 'boutique_user')
                    ->withPivot('role', 'actif')
                    ->withTimestamps();
    }

    // Vérifier si vendeur peut être ajouté
    public function peutAjouterVendeur(): bool
    {
        $gerant = $this->gerant;
        if (!$gerant || !$gerant->forfaitActif) return false;
        return $this->vendeurs()->count() < $gerant->forfaitActif->nb_vendeurs_par_boutique;
    }
}