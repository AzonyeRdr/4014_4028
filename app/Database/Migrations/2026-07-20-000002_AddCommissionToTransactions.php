<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommissionToTransactions extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('commission', 'transactions')) {
            $this->forge->addColumn('transactions', [
                'commission' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0.00,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('commission', 'transactions')) {
            $this->forge->dropColumn('transactions', 'commission');
        }
    }
}
