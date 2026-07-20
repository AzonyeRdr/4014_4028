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

        // 5. Seed transactions
        $transactions = [
            // Transferts (ID 1 à 6)
            ['num' => '0321122334', 'montant' => 5000.00, 'dateTransaction' => '2026-07-20 08:00:00'],
            ['num' => '0334455667', 'montant' => -5000.00, 'dateTransaction' => '2026-07-20 08:02:00'],
            ['num' => '0342233445', 'montant' => 12000.00, 'dateTransaction' => '2026-07-20 09:15:00'],
            ['num' => '0325566778', 'montant' => -12000.00, 'dateTransaction' => '2026-07-20 09:16:00'],
            ['num' => '0388887766', 'montant' => 60000.00, 'dateTransaction' => '2026-07-20 10:30:00'],
            ['num' => '0347788990', 'montant' => -60000.00, 'dateTransaction' => '2026-07-20 10:31:00'],
            
            // Dépôts simples (ID 7 à 9)
            ['num' => '0321122334', 'montant' => 15000.00, 'dateTransaction' => '2026-07-20 11:00:00'],
            ['num' => '0338899001', 'montant' => 50000.00, 'dateTransaction' => '2026-07-20 11:15:00'],
            ['num' => '0371112233', 'montant' => 150000.00, 'dateTransaction' => '2026-07-20 12:00:00'],
            
            // Retraits simples (ID 10 à 12)
            ['num' => '0342233445', 'montant' => -2500.00, 'dateTransaction' => '2026-07-20 13:45:00'],
            ['num' => '0334455667', 'montant' => -10000.00, 'dateTransaction' => '2026-07-20 14:20:00'],
            ['num' => '0388887766', 'montant' => -45000.00, 'dateTransaction' => '2026-07-20 15:10:00'],
        ];
        $this->db->table('transactions')->insertBatch($transactions);

        // 6. Seed transferts
        $transferts = [
            ['idTransactionE' => 2, 'idTransactionD' => 1],
            ['idTransactionE' => 4, 'idTransactionD' => 3],
            ['idTransactionE' => 6, 'idTransactionD' => 5],
        ];
        $this->db->table('transferts')->insertBatch($transferts);
    }
}