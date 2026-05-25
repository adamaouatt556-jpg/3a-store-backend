<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'produit_id',
        'quantite',
        'quantite_reservee',
    ];

    protected function casts(): array
    {
        return [
            'quantite'          => 'integer',
            'quantite_reservee' => 'integer',
        ];
    }

    // Un stock appartient à un produit
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // Quantité réellement disponible
    public function quantiteDisponible(): int
    {
        return $this->quantite - $this->quantite_reservee;
    }
}