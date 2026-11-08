<?php

namespace App\Controllers\Bloc7;

use App\Controllers\BaseController;
use App\Services\Bloc7\TypesCongesService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class TypesCongesController extends BaseController
{
    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        return view('bloc7/types_conges/index', ['pageTitle' => 'Types de congé', 'q' => $search, 'rows' => (new TypesCongesService())->listing($search)]);
    }

    public function form(?int $id = null): string
    {
        $row = $id ? (new TypesCongesService())->find($id) : null;
        if ($id && ! $row) throw PageNotFoundException::forPageNotFound();
        return view('bloc7/types_conges/form', ['pageTitle' => $id ? 'Modifier un type de congé' : 'Créer un type de congé', 'typeConge' => $row]);
    }

    public function save(?int $id = null)
    {
        try {
            (new TypesCongesService())->save($this->request->getPost() ?? [], (int) session()->get('auth_employee_id'), $id);
            return redirect()->to(site_url('bloc7/types-conges'))->with('success', 'Type de congé enregistré.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function disable(int $id)
    {
        try {
            (new TypesCongesService())->disable($id, (int) session()->get('auth_employee_id'));
            return redirect()->to(site_url('bloc7/types-conges'))->with('success', 'Type désactivé. Les enregistrements existants sont conservés.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
