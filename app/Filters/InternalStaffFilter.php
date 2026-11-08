<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Réserve les écrans internes qui ne font pas partie du portail salarié. */
class InternalStaffFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('role') === 'salarie') {
            return service('response')->setStatusCode(403)->setBody('Cette page n’est pas accessible depuis le portail salarié.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
