<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'actif',
        'photo',
        'telephone',
        'valide',
        'forfait_actif_id',
        'must_change_password',
        'temp_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'actif'             => 'boolean',
            'valide'            => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    // Relations
    public function boutiques()
    {
        return $this->hasMany(Boutique::class, 'gerant_id');
    }

    public function boutiquesAssignees()
    {
        return $this->belongsToMany(Boutique::class, 'boutique_user')
                    ->withPivot('role', 'actif')
                    ->withTimestamps();
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    public function abonnementActif()
    {
        return $this->hasOne(Abonnement::class)
                    ->where('statut', 'actif')
                    ->latest();
    }

    public function forfaitActif()
    {
        return $this->belongsTo(Forfait::class, 'forfait_actif_id');
    }

    // Vérifier le rôle
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isGerant(): bool     { return $this->role === 'gerant';      }
    public function isVendeur(): bool    { return $this->role === 'vendeur';     }

    // Vérifier les limites du forfait
    public function peutCreerBoutique(): bool
    {
        if ($this->isSuperAdmin()) return true;
        
        // Si pas de forfait, autoriser quand même (dev ou gérant en attente)
        if (!$this->forfait_actif_id) return true;
        
        $nbBoutiques = $this->boutiques()->count();
        $limite      = $this->forfaitActif->nb_boutiques;

        // -1 = illimité
        return $limite === -1 || $nbBoutiques < $limite;
    }

    public function peutAjouterVendeur(Boutique $boutique): bool
    {
        if ($this->isSuperAdmin()) return true;
        if (!$this->forfaitActif) return false;

        $nbVendeurs = $boutique->vendeurs()->count();
        return $nbVendeurs < $this->forfaitActif->nb_vendeurs_par_boutique;
    }
}