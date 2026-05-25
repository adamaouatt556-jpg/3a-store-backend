<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forfait extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'type',
        'prix',
        'nb_boutiques',
        'nb_vendeurs_par_boutique',
        'duree_jours',
        'description',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'prix'                     => 'decimal:2',
            'nb_boutiques'             => 'integer',
            'nb_vendeurs_par_boutique' => 'integer',
            'duree_jours'              => 'integer',
            'actif'                    => 'boolean',
        ];
    }

    // Un forfait a plusieurs abonnements
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    // Libellé de la durée
    public function getDureeLibelleAttribute(): string
    {
        return match($this->type) {
            'mensuel'      => '1 mois',
            'trimestriel'  => '3 mois',
            'semestriel'   => '6 mois',
            'annuel'       => '1 an',
            default        => $this->duree_jours . ' jours',
        };
    }

    // Libellé boutiques
    public function getBoutiquesLibelleAttribute(): string
    {
        return $this->nb_boutiques === -1
            ? 'Illimitées'
            : $this->nb_boutiques . ' boutique(s)';
    }
}