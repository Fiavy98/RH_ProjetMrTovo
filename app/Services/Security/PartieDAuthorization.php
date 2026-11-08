<?php

namespace App\Services\Security;

final class PartieDAuthorization
{
    public static function allows(string $role): bool
    {
        return in_array($role, ['rh', 'admin'], true);
    }
}
