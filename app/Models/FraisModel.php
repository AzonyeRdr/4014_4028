<?php

namespace App\Models;

use CodeIgniter\Model;

class FraisModel extends Model
{
    protected $table = 'frais';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['montantMin', 'montantMax', 'montantFrais'];

}
