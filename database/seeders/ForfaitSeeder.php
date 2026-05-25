<?php

namespace Database\Seeders;

use App\Models\Forfait;
use Illuminate\Database\Seeder;

class ForfaitSeeder extends Seeder
{
    public function run(): void
    {
        $forfaits = [
            [
                'nom'                      => 'Mensuel',
                'type'                     => 'mensuel',
                'prix'                     => 15000,
                'nb_boutiques'             => 1,
                'nb_vendeurs_par_boutique' => 3,
                'duree_jours'              => 30,
                'description'              => '1 boutique · 3 vendeurs · 30 jours',
                'actif'                    => true,
            ],
            [
                'nom'                      => 'Trimestriel',
                'type'                     => 'trimestriel',
                'prix'                     => 40000,
                'nb_boutiques'             => 2,
                'nb_vendeurs_par_boutique' => 3,
                'duree_jours'              => 90,
                'description'              => '2 boutiques · 3 vendeurs · 90 jours',
                'actif'                    => true,
            ],
            [
                'nom'                      => 'Semestriel',
                'type'                     => 'semestriel',
                'prix'                     => 75000,
                'nb_boutiques'             => 3,
                'nb_vendeurs_par_boutique' => 3,
                'duree_jours'              => 180,
                'description'              => '3 boutiques · 3 vendeurs · 180 jours',
                'actif'                    => true,
            ],
            [
                'nom'                      => 'Annuel',
                'type'                     => 'annuel',
                'prix'                     => 120000,
                'nb_boutiques'             => -1,
                'nb_vendeurs_par_boutique' => 3,
                'duree_jours'              => 365,
                'description'              => 'Boutiques illimitées · 3 vendeurs · 365 jours',
                'actif'                    => true,
            ],
        ];

        foreach ($forfaits as $forfait) {
            Forfait::firstOrCreate(
                ['type' => $forfait['type']],
                $forfait
            );
        }
    }
}