<?php

namespace App\Entities;

use App\Entities\Cast\PgBoolCast;
use CodeIgniter\Entity\Entity;

/**
 * Entité de base du projet : toutes les entités SIRH en héritent.
 * Elle ajoute seulement le cast 'pgbool' (booléens PostgreSQL).
 */
abstract class SirhEntity extends Entity
{
    protected $castHandlers = [
        'pgbool' => PgBoolCast::class,
    ];
}
