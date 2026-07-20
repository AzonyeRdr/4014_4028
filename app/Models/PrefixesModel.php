<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixesModel extends Model
{
    protected $table = 'prefixes';
    protected $primaryKey = 'num';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['num', 'operateur'];

}
