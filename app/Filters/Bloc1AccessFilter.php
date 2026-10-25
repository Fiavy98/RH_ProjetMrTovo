<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Bloc1AccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = (string) session()->get('role');
        $path = trim(uri_string(), '/');

        if ($role === 'admin' || $path === 'bloc1' || ! str_starts_with($path, 'bloc1/')) {
            return;
        }

        $allowed = match ($role) {
            'rh' => ['rh-review', 'requests', 'dashboard', 'demande-form', 'positions', 'history'],
            'finance' => ['finance-review', 'requests', 'dashboard', 'positions', 'history'],
            'direction' => ['rh-review', 'finance-review', 'direction-review', 'requests', 'dashboard', 'positions', 'history'],
            default => ['requests', 'dashboard', 'demande-form', 'positions'],
        };
        $segment = explode('/', substr($path, 6))[0] ?? '';

        if ($segment !== '' && ! in_array($segment, $allowed, true)) {
            return redirect()->to(site_url('bloc1/'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
