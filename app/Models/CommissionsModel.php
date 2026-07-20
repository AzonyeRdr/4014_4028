<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionsModel extends Model
{
    protected $table = 'commissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['pourcentage'];
}
