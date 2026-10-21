<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model de base du projet : tous les Models SIRH en héritent.
 *
 * - Clé primaire `id` générée par PostgreSQL (GENERATED ALWAYS AS IDENTITY) : on ne l'envoie jamais.
 * - Pas de timestamps automatiques : les colonnes cree_le / date_* ont leur valeur par défaut en base.
 * - Pas de suppression logique pour l'instant.
 */
abstract class SirhModel extends Model
{
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $protectFields    = true;
}
