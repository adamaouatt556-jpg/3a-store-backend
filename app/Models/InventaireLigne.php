<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventaireLigne extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventaire_id',
        'produit_id',
        'stock_systeme',
        'stock_reel',
        'ecart',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'stock_systeme' => 'integer',
            'stock_reel'    => 'integer',
            'ecart'         => 'integer',
        ];
    }

    // Relations
    public function inventaire()
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}