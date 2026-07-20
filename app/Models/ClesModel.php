<?php

namespace App\Models;

use CodeIgniter\Model;

class ClesModel extends Model
{
    protected $table = 'cles';
    protected $primaryKey = 'num';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['num', 'operateur'];
}
