<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'boutique_id',
        'user_id',
        'statut',
        'note',
        'valide_le',
    ];

    protected function casts(): array
    {
        return [
            'valide_le' => 'datetime',
        ];
    }

    // Relations
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lignes()
    {
        return $this->hasMany(InventaireLigne::class);
    }

    // Valider l'inventaire
    public function valider(): void
    {
        foreach ($this->lignes as $ligne) {
            if ($ligne->ecart !== 0) {
                // Créer un mouvement d'ajustement
                MouvementStock::create([
                    'produit_id'    => $ligne->produit_id,
                    'user_id'       => $this->user_id,
                    'type'          => 'ajustement',
                    'quantite'      => $ligne->stock_reel,
                    'motif'         => 'Inventaire #' . $this->id,
                    'note'          => $ligne->note,
                ]);
            }
        }

        $this->update([
            'statut'    => 'valide',
            'valide_le' => now(),
        ]);
    }
}