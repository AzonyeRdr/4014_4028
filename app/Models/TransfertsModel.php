<?php

namespace App\Models;

use CodeIgniter\Model;

class TransfertsModel extends Model
{
    protected $table = 'transferts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['idTransactionE', 'idTransactionD'];

}
