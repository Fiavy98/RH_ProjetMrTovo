<?php

namespace App\Controllers\Bloc7;

use App\Controllers\BaseController;
use App\Services\Bloc7\AbsencesService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class AbsencesController extends BaseController
{
    public function index(): string
    {
        $filters = $this->request->getGet() ?? [];
        $service = new AbsencesService();
        return view('bloc7/absences/index', [
            'pageTitle' => 'Absences et justificatifs', 'filters' => $filters,
            'rows' => $service->listing($filters), 'options' => $service->options(),
        ]);
    }

    public function form(?int $id = null): string
    {
        $service = new AbsencesService();
        $row = $id ? $service->detail($id) : null;
        if ($id && ! $row) throw PageNotFoundException::forPageNotFound();
        return view('bloc7/absences/form', ['pageTitle' => $id ? 'Modifier une absence' : 'Enregistrer une absence', 'absence' => $row, 'options' => $service->options()]);
    }

    public function save(?int $id = null)
    {
        try {
            $savedId = (new AbsencesService())->save($this->request->getPost() ?? [], $this->request->getFile('justificatif'), (int) session()->get('auth_employee_id'), $id);
            return redirect()->to(site_url('bloc7/absences/' . $savedId))->with('success', 'Absence enregistrée avec sa traçabilité.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function detail(int $id): string
    {
        $row = (new AbsencesService())->detail($id);
        if (! $row) throw PageNotFoundException::forPageNotFound();
        return view('bloc7/absences/detail', ['pageTitle' => 'Détail d’une absence', 'absence' => $row]);
    }

    public function justificatif(int $id)
    {
        $row = (new AbsencesService())->detail($id);
        if (! $row || empty($row['justificatif_nom_stocke'])) throw PageNotFoundException::forPageNotFound();
        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'absences' . DIRECTORY_SEPARATOR . basename($row['justificatif_nom_stocke']);
        if (! is_file($path)) throw PageNotFoundException::forPageNotFound();
        $downloadName = preg_replace('/[^A-Za-z0-9._ -]/u', '_', basename((string) $row['justificatif_nom_original'])) ?: 'justificatif';
        return $this->response->download($path, null)->setFileName($downloadName);
    }
}
