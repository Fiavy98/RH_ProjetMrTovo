<?php

namespace App\Controllers\Bloc5;

use App\Services\Bloc5\DocumentsService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class DocumentsController extends Bloc5PageController
{
    private DocumentsService $service;

    public function __construct()
    {
        $this->service = new DocumentsService();
    }

    public function index(?int $employeeId = null): string
    {
        $type = trim((string) ($this->request->getGet('type') ?? ''));

        if ($employeeId === null) {
            $search = trim((string) ($this->request->getGet('q') ?? ''));

            return view('bloc5/documents', [
                'pageTitle' => 'Documents administratifs',
                'blockTitle' => self::BLOCK_TITLE,
                'employee' => null,
                'documents' => [],
                'contracts' => [],
                'types' => $this->service->documentTypes(),
                'filter' => $type,
                'message' => session()->getFlashdata('message'),
                'error' => session()->getFlashdata('error'),
                'search' => $search,
                'searchResults' => $this->service->searchEmployees($search),
            ]);
        }

        $employee = $this->service->employee($employeeId);
        if ($employee === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('bloc5/documents', [
            'pageTitle' => 'Documents administratifs',
            'blockTitle' => self::BLOCK_TITLE,
            'employee' => $employee,
            'documents' => $this->service->documentsForEmployee($employeeId, $type),
            'contracts' => $this->service->contractsForEmployee($employeeId),
            'types' => $this->service->documentTypes(),
            'filter' => $type,
            'message' => session()->getFlashdata('message'),
            'error' => session()->getFlashdata('error'),
            'search' => '',
            'searchResults' => [],
        ]);
    }

    public function store(int $employeeId): RedirectResponse
    {
        $result = $this->service->addDocument($employeeId, $this->request->getPost(), $this->request->getFile('fichier'));

        if ($result['ok']) {
            session()->setFlashdata('message', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
        }

        return redirect()->to(site_url('bloc5/documents/' . $employeeId));
    }

    public function update(int $employeeId, int $documentId): RedirectResponse
    {
        $result = $this->service->updateDocument(
            $employeeId,
            $documentId,
            $this->request->getPost(),
            $this->request->getFile('fichier')
        );

        if ($result['ok']) {
            session()->setFlashdata('message', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
        }

        return redirect()->to(site_url('bloc5/documents/' . $employeeId));
    }

    public function delete(int $employeeId, int $documentId): RedirectResponse
    {
        $result = $this->service->deleteDocument($employeeId, $documentId);

        if ($result['ok']) {
            session()->setFlashdata('message', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
        }

        return redirect()->to(site_url('bloc5/documents/' . $employeeId));
    }

    public function download(int $employeeId, int $documentId): ResponseInterface|RedirectResponse
    {
        $download = $this->service->downloadDocument($employeeId, $documentId);
        if ($download === null) {
            session()->setFlashdata('error', 'Ce document est introuvable ou n’appartient pas au salarié sélectionné.');

            return redirect()->to(site_url('bloc5/documents/' . $employeeId));
        }

        return $this->response->download($download['path'], null)->setFileName($download['name']);
    }
}
