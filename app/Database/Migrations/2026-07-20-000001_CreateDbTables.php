<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDbTables extends Migration
{
    public function up()
    {
        // Table: prefixes
        $this->forge->addField([
            'num' => [
                'type'       => 'VARCHAR',
                'constraint' => '3',
                'unique'     => true,
            ],
            'operateur' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
        ]);
        $this->forge->createTable('prefixes');

        // Table: numeros
        $this->forge->addField([
            'num' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'unique'     => true,
            ],
        ]);
        $this->forge->createTable('numeros');

        // Table: cles
        $this->forge->addField([
            'num' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'unique'     => true,
            ],
            'operateur' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
        ]);
        $this->forge->createTable('cles');

        // Table: frais
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'montantMin' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'montantMax' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'montantFrais' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('frais');

        // Table: transactions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'num' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
            ],
            'montant' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'frais' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'dateTransaction' => [
                'type' => 'TIMESTAMP',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('transactions');

        // Table: transferts
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'idTransactionE' => [
                'type' => 'INT',
            ],
            'idTransactionD' => [
                'type' => 'INT',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idTransactionE', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('idTransactionD', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transferts');
    }

    public function down()
    {
        $this->forge->dropTable('transferts', true);
        $this->forge->dropTable('transactions', true);
        $this->forge->dropTable('frais', true);
        $this->forge->dropTable('cles', true);
        $this->forge->dropTable('numeros', true);
        $this->forge->dropTable('prefixes', true);
    }
}
