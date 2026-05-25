<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    use HasFactory;

    // Forcer le bon nom de table
    protected $table = 'mouvements_stock';

    protected $fillable = [
        'produit_id',
        'user_id',
        'type',
        'quantite',
        'prix_unitaire',
        'motif',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantite'      => 'integer',
            'prix_unitaire' => 'decimal:2',
        ];
    }

    // Un mouvement appartient à un produit
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // Un mouvement appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mettre à jour le stock automatiquement
    protected static function boot()
    {
        parent::boot();

        static::created(function ($mouvement) {
            $stock = Stock::firstOrCreate(
                ['produit_id' => $mouvement->produit_id],
                ['quantite' => 0]
            );

            if (in_array($mouvement->type, ['entree', 'retour'])) {
                $stock->increment('quantite', $mouvement->quantite);
            } elseif (in_array($mouvement->type, ['sortie', 'perte'])) {
                $stock->decrement('quantite', $mouvement->quantite);
            } elseif ($mouvement->type === 'ajustement') {
                $stock->update(['quantite' => $mouvement->quantite]);
            }
        });
    }
}