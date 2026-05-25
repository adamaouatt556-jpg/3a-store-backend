<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'nom',
        'description',
        'categorie_id',
        'boutique_id',
        'prix_achat',
        'prix_vente',
        'stock_alerte',
        'unite',
        'images',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'prix_achat'  => 'decimal:2',
            'prix_vente'  => 'decimal:2',
            'images'      => 'array',
            'actif'       => 'boolean',
        ];
    }

    // Un produit appartient à une catégorie
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    // Un produit appartient à une boutique
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    // Un produit a un stock
    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    // Un produit a plusieurs mouvements
    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class);
    }

    // Vérifier si le stock est bas
    public function isStockBas(): bool
    {
        return $this->stock && 
               $this->stock->quantite <= $this->stock_alerte;
    }

    // Vérifier si rupture de stock
    public function isRupture(): bool
    {
        return $this->stock && 
               $this->stock->quantite === 0;
    }
}