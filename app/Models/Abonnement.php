<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Abonnement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forfait_id',
        'statut',
        'montant_paye',
        'date_debut',
        'date_fin',
        'valide_par',
        'valide_le',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'montant_paye' => 'decimal:2',
            'date_debut'   => 'date',
            'date_fin'     => 'date',
            'valide_le'    => 'datetime',
        ];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function forfait()
    {
        return $this->belongsTo(Forfait::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // Valider l'abonnement
    public function valider(User $admin): void
    {
        $debut = Carbon::now();
        $fin   = $debut->copy()->addDays($this->forfait->duree_jours);

        $this->update([
            'statut'     => 'actif',
            'date_debut' => $debut,
            'date_fin'   => $fin,
            'valide_par' => $admin->id,
            'valide_le'  => now(),
        ]);

        // Mettre à jour le forfait actif de l'utilisateur
        $this->user->update([
            'forfait_actif_id' => $this->forfait_id,
            'valide'           => true,
        ]);
    }

    // Vérifier si expiré
    public function estExpire(): bool
    {
        return $this->date_fin && Carbon::now()->isAfter($this->date_fin);
    }
}