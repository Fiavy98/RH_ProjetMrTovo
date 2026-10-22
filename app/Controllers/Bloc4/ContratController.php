<?php

namespace App\Controllers\Bloc4;

use App\Controllers\BaseController;
use App\Services\Bloc4\ContratService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ContratController extends BaseController
{
    private ContratService $contrats;

    public function __construct()
    {
        $this->contrats = new ContratService();
    }

    public function index(): string
    {
        return view('bloc4/contrats/index', [
            'pageTitle' => 'Liste des contrats',
            'contrats' => $this->contrats->liste($this->request->getGet() ?? []),
            'filters' => $this->request->getGet() ?? [],
            'types' => $this->contrats->options()['types'],
        ]);
    }

    public function createForm(): string
    {
        return view('bloc4/contrats/form', [
            'pageTitle' => 'Nouveau contrat', 'contrat' => null,
            'options' => $this->contrats->options(),
        ]);
    }

    public function editForm(int $id)
    {
        $contrat = $this->contrats->detail($id);
        if ($contrat === null) throw PageNotFoundException::forPageNotFound();
        if ($contrat['statut'] !== 'brouillon') {
            session()->setFlashdata('error', 'Seul un contrat brouillon peut être modifié.');
            return redirect()->to(site_url('bloc4/contract-detail/' . $id));
        }
        return view('bloc4/contrats/form', ['pageTitle' => 'Modifier le contrat', 'contrat' => $contrat, 'options' => $this->contrats->options()]);
    }

    public function save(?int $id = null)
    {
        $result = $this->contrats->enregistrer($this->request->getPost() ?? [], $id);
        if (! $result['ok']) {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
        session()->setFlashdata('success', $result['message']);
        return redirect()->to(site_url('bloc4/contract-detail/' . $result['id']));
    }

    public function detail(int $id): string
    {
        $contrat = $this->contrats->detail($id);
        if ($contrat === null) throw PageNotFoundException::forPageNotFound();
        return view('bloc4/contrats/detail', ['pageTitle' => 'Fiche contrat', 'contrat' => $contrat]);
    }

    public function transition(int $id, string $action)
    {
        $result = $this->contrats->transition($id, $action);
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc4/contract-detail/' . $id));
    }

    public function renewals(): string
    {
        return view('bloc4/contrats/renewals', ['pageTitle' => 'Renouvellements', 'contrats' => $this->contrats->renouvelables()]);
    }

    public function settings(): string
    {
        return view('bloc4/contrats/settings', ['pageTitle' => 'Paramètres des contrats', 'types' => $this->contrats->parametres()]);
    }

    public function renewalForm(int $id): string
    {
        $previous = $this->contrats->detail($id);
        if ($previous === null) throw PageNotFoundException::forPageNotFound();
        return view('bloc4/contrats/renewal', [
            'pageTitle' => 'Créer un renouvellement',
            'contrat' => $previous,
            'dateDebut' => $this->contrats->dateDebutRenouvellement($id),
        ]);
    }

    public function renew(int $id)
    {
        $result = $this->contrats->renouveler($id, $this->request->getPost() ?? []);
        if (! $result['ok']) {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
        session()->setFlashdata('success', $result['message']);
        return redirect()->to(site_url('bloc4/contract-detail/' . $result['id']));
    }
}
