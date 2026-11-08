<?php

namespace App\Filters;

use App\Services\Security\PartieDAuthorization;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PartieDAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (PartieDAuthorization::allows((string) session()->get('role'))) {
            return null;
        }

        return service('response')->setStatusCode(403)->setBody('Accès réservé aux Ressources humaines et aux administrateurs.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
