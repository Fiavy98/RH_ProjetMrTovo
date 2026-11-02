<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Accès par rôle aux Blocs 2, 3 et 4. Le Bloc 1 conserve son filtre dédié. */
class BlocRoleAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = (string) session()->get('role');
        $path = trim(uri_string(), '/');
        $method = strtoupper($request->getMethod());

        if ($role === 'admin') {
            return null;
        }

        $allowed = match (true) {
            preg_match('#^bloc2(?:/|$)#', $path) === 1 => $this->bloc2Allowed($role, $path, $method),
            preg_match('#^bloc3(?:/|$)#', $path) === 1 => $this->bloc3Allowed($role, $path),
            preg_match('#^bloc4(?:/|$)#', $path) === 1 => $this->bloc4Allowed($role, $path, $method),
            default => false,
        };

        if ($allowed) {
            return null;
        }

        return service('response')
            ->setStatusCode(403)
            ->setBody('Accès interdit pour ce rôle.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function bloc2Allowed(string $role, string $path, string $method): bool
    {
        if ($role === 'rh') {
            return true;
        }

        if ($role !== 'manager') {
            return false;
        }

        if ($method === 'GET' && $path === 'bloc2/interviews') {
            return true;
        }

        if ($path === 'bloc2/evaluations' || preg_match('#^bloc2/evaluations/\d+$#', $path)) {
            return $method === 'GET';
        }

        return $method === 'POST' && $path === 'bloc2/evaluations/save';
    }

    private function bloc3Allowed(string $role, string $path): bool
    {
        if ($role === 'rh') {
            return ! str_starts_with($path, 'bloc3/hire-validations');
        }

        return $role === 'direction' && str_starts_with($path, 'bloc3/hire-validations');
    }

    private function bloc4Allowed(string $role, string $path, string $method): bool
    {
        if ($role === 'rh') {
            return true;
        }

        if ($role !== 'manager' || $method !== 'GET') {
            return false;
        }

        return $path === 'bloc4/onboardings'
            || preg_match('#^bloc4/onboarding-detail/\d+$#', $path) === 1;
    }
}
