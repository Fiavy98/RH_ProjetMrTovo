<?php

namespace App\Controllers\Bloc6;

use App\Controllers\BaseController;
use App\Services\Bloc6\Bloc6Service;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** Contrôleur de navigation statique du Bloc 6. */
class Bloc6Controller extends BaseController
{
    private Bloc6Service $pages;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->pages = new Bloc6Service();
    }

    public function index()
    {
        return redirect()->to(site_url('bloc6/horaires'));
    }

    public function show(string $slug): string
    {
        $screen = $this->pages->screen($slug);
        if ($screen === null) throw PageNotFoundException::forPageNotFound();

        return view('shared/module_scaffold', [
            'pageTitle' => $screen['title'],
            'blockTitle' => 'Bloc 6 · Temps de travail et pointage',
            'screen' => $screen,
            'screens' => $this->pages->screens(),
        ]);
    }
}
