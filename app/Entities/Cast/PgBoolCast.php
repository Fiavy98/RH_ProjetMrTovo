<?php

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

/**
 * PostgreSQL renvoie les booléens sous forme de 't' / 'f'.
 * Le cast 'bool' standard transformerait 'f' en true : on gère donc ces valeurs ici.
 */
class PgBoolCast extends BaseCast
{
    public static function get($value, array $params = []): bool
    {
        return in_array($value, [true, 1, '1', 't', 'true', 'TRUE', 'on', 'yes'], true);
    }

    public static function set($value, array $params = []): bool
    {
        return in_array($value, [true, 1, '1', 't', 'true', 'TRUE', 'on', 'yes'], true);
    }
}
