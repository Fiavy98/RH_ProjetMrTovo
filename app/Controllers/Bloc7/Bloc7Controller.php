<?php

namespace App\Controllers\Bloc7;

use App\Controllers\BaseController;
use App\Services\Bloc7\Bloc7Service;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** Contrôleur de navigation statique du Bloc 7. */
class Bloc7Controller extends BaseController
{
    private Bloc7Service $pages;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->pages = new Bloc7Service();
    }

    public function index()
    {
        return redirect()->to(site_url('bloc7/demandes'));
    }

    public function show(string $slug): string
    {
        $screen = $this->pages->screen($slug);
        if ($screen === null) throw PageNotFoundException::forPageNotFound();

        return view('shared/module_scaffold', [
            'pageTitle' => $screen['title'],
            'blockTitle' => 'Bloc 7 · Congés et absences',
            'screen' => $screen,
            'screens' => $this->pages->screens(),
        ]);
    }
}
