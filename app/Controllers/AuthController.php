<?php

namespace App\Controllers;

use App\Models\EmployeModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class AuthController extends BaseController
{
    public function index()
    {
        if (session()->has('auth_employee_id')) {
            return redirect()->to(site_url('/'));
        }

        $employees = $this->availableEmployees();

        return view('auth/select', [
            'pageTitle' => 'Choisir un utilisateur',
            'employees' => $employees,
            'error'     => session()->getFlashdata('error'),
        ]);
    }

    public function select()
    {
        $employeeId = (int) $this->request->getPost('employee_id');
        $employee = $this->availableEmployees($employeeId)[0] ?? null;

        if ($employee === null) {
            session()->setFlashdata('error', 'Veuillez sélectionner un utilisateur actif.');
            return redirect()->to(site_url('auth'));
        }

        session()->regenerate(true);
        session()->set([
            'auth_employee_id' => (int) $employee->id,
            'role'             => $this->roleKey((string) $employee->nom_role),
            'auth_user'        => [
                'id'         => (int) $employee->id,
                'name'       => trim($employee->prenom . ' ' . $employee->nom),
                'label'      => (string) $employee->nom_role,
                'department' => (string) $employee->nom_departement,
                'initials'    => strtoupper(mb_substr($employee->prenom, 0, 1) . mb_substr($employee->nom, 0, 1)),
            ],
        ]);

        return redirect()->to(site_url('/'));
    }

    public function logout()
    {
        session()->remove(['auth_employee_id', 'role', 'auth_user']);
        return redirect()->to(site_url('auth'));
    }

    private function availableEmployees(?int $id = null): array
    {
        $model = new EmployeModel();
        $model->select('employe.*, role.nom_role, departement.nom_departement')
            ->join('role', 'role.id = employe.role_id')
            ->join('departement', 'departement.id = employe.departement_id')
            ->where('employe.actif', true)
            ->where('employe.role_id IS NOT NULL', null, false)
            ->orderBy('role.nom_role', 'ASC')
            ->orderBy('employe.nom', 'ASC');

        if ($id !== null) {
            $model->where('employe.id', $id);
        }

        return $model->findAll();
    }

    private function roleKey(string $role): string
    {
        $role = mb_strtolower(trim($role));

        return match (true) {
            str_contains($role, 'administr') || str_contains($role, 'super') => 'admin',
            str_contains($role, 'direction') => 'direction',
            str_contains($role, 'finance') => 'finance',
            str_contains($role, 'rh') || str_contains($role, 'ressources') => 'rh',
            default => 'manager',
        };
    }
}
