<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed prefixes
        $prefixes = [
            ['num' => '032', 'operateur' => 'Orange'],
            ['num' => '033', 'operateur' => 'Airtel'],
            ['num' => '034', 'operateur' => 'Yas'],
            ['num' => '037', 'operateur' => 'Orange'],
            ['num' => '038', 'operateur' => 'Yas'],
        ];
        $this->db->table('prefixes')->insertBatch($prefixes);

        // 2. Seed cles
        $cles = [
            ['num' => '0000000000', 'operateur' => 'Orange'],
            ['num' => '1111111111', 'operateur' => 'Yas'],
            ['num' => '2222222222', 'operateur' => 'Airtel'],
        ];
        $this->db->table('cles')->insertBatch($cles);

        // 3. Seed frais
        $frais = [
            ['montantMin' => 100, 'montantMax' => 1000, 'montantFrais' => 50],
            ['montantMin' => 1001, 'montantMax' => 5000, 'montantFrais' => 50],
            ['montantMin' => 5001, 'montantMax' => 10000, 'montantFrais' => 100],
            ['montantMin' => 10001, 'montantMax' => 25000, 'montantFrais' => 200],
            ['montantMin' => 25001, 'montantMax' => 50000, 'montantFrais' => 400],
            ['montantMin' => 50001, 'montantMax' => 100000, 'montantFrais' => 800],
            ['montantMin' => 100001, 'montantMax' => 250000, 'montantFrais' => 1500],
            ['montantMin' => 250001, 'montantMax' => 500000, 'montantFrais' => 1500],
            ['montantMin' => 500001, 'montantMax' => 1000000, 'montantFrais' => 2500],
            ['montantMin' => 1000001, 'montantMax' => 2000000, 'montantFrais' => 3000],
        ];
        $this->db->table('frais')->insertBatch($frais);

        // 4. Seed numeros
        $numeros = [
            ['num' => '0321122334'],
            ['num' => '0325566778'],
            ['num' => '0334455667'],
            ['num' => '0338899001'],
            ['num' => '0342233445'],
            ['num' => '0347788990'],
            ['num' => '0371112233'],
            ['num' => '0388887766'],
        ];
        $this->db->table('numeros')->insertBatch($numeros);
    }
}
