<?php

namespace App\Controllers;

use App\Services\BlocPageService;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Contrôleur commun de rendu ; chaque bloc fournit son contrôleur et son service propres. */
abstract class BlocPageController extends BaseController
{
    protected int $bloc;
    abstract protected function pages(): BlocPageService;

    public function index()
    {
        $first = array_key_first($this->pages()->screens());
        return redirect()->to(site_url('bloc' . $this->bloc . '/' . $first));
    }

    public function show(string $slug): string
    {
        $page = $this->pages()->page($slug);
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        return view('bloc' . $this->bloc . '/page', [
            'pageTitle' => $page['title'],
            'bloc' => $this->bloc,
            'page' => $page,
            'screens' => $this->pages()->screens(),
        ]);
    }

    public function save(string $slug)
    {
        $result = $this->pages()->save($slug, $this->request->getPost() ?? []);
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc' . $this->bloc . '/' . $slug));
    }
}
