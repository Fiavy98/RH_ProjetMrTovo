<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'Client';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'idOperateur',
        'num',
        'solde',
        'dateCreation'
    ];
}