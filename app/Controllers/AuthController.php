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
        $profiles = $this->demoProfiles($employees);

        return view('auth/select', [
            'pageTitle' => 'Connexion de démonstration',
            'profiles'  => $profiles,
            'hasProfiles' => count(array_filter(array_column($profiles, 'available'))) > 0,
            'error'     => session()->getFlashdata('error'),
        ]);
    }

    public function select()
    {
        $profile = (string) $this->request->getPost('role');
        $login = trim((string) $this->request->getPost('login'));
        $password = (string) $this->request->getPost('password');
        $profileRoles = [
            'manager' => 'manager',
            'responsable' => 'manager',
            'rh' => 'rh',
            'finance' => 'finance',
            'direction' => 'direction',
            'admin' => 'admin',
        ];

        if (! hash_equals('demo', $login) || ! hash_equals('demo', $password)) {
            session()->setFlashdata('error', 'Identifiant ou mot de passe de démonstration invalide. Utilisez demo / demo.');
            return redirect()->to(site_url('auth'));
        }

        $requiredRole = $profileRoles[$profile] ?? null;
        $employee = null;
        if ($requiredRole !== null) {
            foreach ($this->availableEmployees() as $candidate) {
                if ($this->roleKey((string) $candidate->nom_role) === $requiredRole) {
                    $employee = $candidate;
                    break;
                }
            }
        }

        if ($employee === null) {
            session()->setFlashdata('error', 'Aucun utilisateur actif n’est configuré pour ce profil.');
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

    private function availableEmployees(): array
    {
        $model = new EmployeModel();
        $model->select('employe.*, role.nom_role, departement.nom_departement')
            ->join('role', 'role.id = employe.role_id')
            ->join('departement', 'departement.id = employe.departement_id')
            ->where('employe.actif', true)
            ->where('employe.role_id IS NOT NULL', null, false)
            ->orderBy('role.nom_role', 'ASC')
            ->orderBy('employe.nom', 'ASC');

        return $model->findAll();
    }

    private function demoProfiles(array $employees): array
    {
        $employeeRoles = array_map(
            fn (object $employee): string => $this->roleKey((string) $employee->nom_role),
            $employees
        );

        $profiles = [
            'manager' => ['label' => 'Manager', 'name' => 'Miora Rakoto', 'employee_role' => 'manager'],
            'responsable' => ['label' => 'Responsable hiérarchique', 'name' => 'Alain R.', 'employee_role' => 'manager'],
            'rh' => ['label' => 'Ressources humaines', 'name' => 'Sofia R.', 'employee_role' => 'rh'],
            'finance' => ['label' => 'Finance', 'name' => 'Hanta M.', 'employee_role' => 'finance'],
            'direction' => ['label' => 'Direction', 'name' => 'Rina T.', 'employee_role' => 'direction'],
            'admin' => ['label' => 'Administrateur', 'name' => 'Admin Démo', 'employee_role' => 'admin'],
        ];

        foreach ($profiles as &$profile) {
            $profile['available'] = in_array($profile['employee_role'], $employeeRoles, true);
            unset($profile['employee_role']);
        }
        unset($profile);

        return $profiles;
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
