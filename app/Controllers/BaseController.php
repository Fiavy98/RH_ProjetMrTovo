<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur de base : tous les controllers du projet en héritent.
 * Il charge les helpers communs ('ui' = composants d'interface du template).
 */
abstract class BaseController extends Controller
{
    /**
     * @var \CodeIgniter\HTTP\CLIRequest|\CodeIgniter\HTTP\IncomingRequest
     */
    protected $request;

    /**
     * Helpers chargés pour tous les controllers (et donc disponibles dans les vues).
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'ui'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }
}
