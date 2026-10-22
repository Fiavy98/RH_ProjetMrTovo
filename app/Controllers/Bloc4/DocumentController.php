<?php

namespace App\Controllers\Bloc4;

use App\Controllers\BaseController;
use App\Services\Bloc4\DocumentService;
use CodeIgniter\Exceptions\PageNotFoundException;

class DocumentController extends BaseController
{
    private DocumentService $documents;

    public function __construct()
    {
        $this->documents = new DocumentService();
    }

    public function index(): string
    {
        return view('bloc4/documents/index', ['pageTitle' => 'Documents', 'documents' => $this->documents->liste()]);
    }

    public function create(): string
    {
        return view('bloc4/documents/form', ['pageTitle' => 'Ajouter un document', 'options' => $this->documents->options()]);
    }

    public function save()
    {
        $result = $this->documents->enregistrer($this->request->getPost() ?? [], $this->request->getFile('fichier'));
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc4/documents'));
    }

    public function download(int $id)
    {
        $document = $this->documents->cheminTelechargement($id);
        if ($document === null) throw PageNotFoundException::forPageNotFound();
        return $this->response->download($document['path'], null)->setFileName($document['name']);
    }
}
