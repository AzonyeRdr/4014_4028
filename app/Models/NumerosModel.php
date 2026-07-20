<?php

namespace App\Models;

use CodeIgniter\Model;

class NumerosModel extends Model
{
    protected $table = 'numeros';
    protected $primaryKey = 'num';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['num'];
    protected $validationRules = [
        'num' => 'required|regex_match[/^0[0-9]{9}$/]|is_unique[numeros.num]',
    ];
}
