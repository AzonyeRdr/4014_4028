<?php

namespace App\Models;

use CodeIgniter\Model;

class EpargneModel extends Model
{
    protected $table = 'epargne';
    protected $returnType = 'array';
    protected $allowedFields = ['num', 'pourcentage', 'montant'];
}
