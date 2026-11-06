<?php

namespace App\Services\Bloc5;

use App\Models\EmployeModel;

class DossiersService
{
    private EmployeModel $employees;

    public function __construct(?EmployeModel $employees = null)
    {
        $this->employees = $employees ?? model(EmployeModel::class);
    }

    public function page(): array
    {
        $filters = ['q' => '', 'departement' => '', 'statut' => ''];
        $result = $this->liste($filters, 1, 10);

        return [
            'slug' => 'dossiers',
            'title' => 'Répertoire des salariés',
            'subtitle' => 'Référentiel central des dossiers salariés',
            'description' => 'Recherche et accès aux dossiers administratifs.',
            'stats' => [
                ['Salariés actifs', (string) $this->employees->countActifs()],
                ['Dossiers', (string) $result['total']],
                ['Départements', (string) count($this->employees->departements())],
            ],
            'columns' => ['Matricule', 'Salarié', 'Département', 'Poste actuel', 'Date d’embauche', 'Statut'],
            'rows' => array_map(static fn (array $employee): array => [
                $employee['matricule'],
                $employee['nom'] . ' ' . $employee['prenom'],
                $employee['departement'] ?? '—',
                $employee['poste_actuel'] ?? '—',
                $employee['date_embauche'] ?? '—',
                $employee['statut'] ?? 'Inactif',
            ], $result['employees']),
        ];
    }

    public function liste(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $total = $this->employees->countFiltered($filters);
        $totalPages = $total === 0 ? 1 : (int) ceil($total / $perPage);
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $employees = $this->employees->filtered($filters, $offset, $perPage);
        $employees = array_map([$this, 'normalizeEmployee'], $employees);

        return [
            'employees' => $employees,
            'departements' => $this->employees->departements(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'pages' => range(1, $totalPages),
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
            'start' => $total === 0 ? 0 : $offset + 1,
            'end' => min($offset + $perPage, $total),
            'filters' => $filters,
        ];
    }

    public function employee(int $id): ?array
    {
        $employee = $this->employees->detail($id);
        if ($employee === null) {
            return null;
        }

        return $this->normalizeEmployee($employee);
    }

    public function normalizeEmployee(array $employee): array
    {
        $employee['statut'] = ((int) ($employee['actif'] ?? 0)) === 1 ? 'Actif' : 'Inactif';
        $employee['date_embauche'] = ! empty($employee['date_embauche']) ? date('d/m/Y', strtotime((string) $employee['date_embauche'])) : '—';
        $employee['photo'] = ! empty($employee['photo']) ? (string) $employee['photo'] : 'assets/images/default-avatar.png';
        $employee['departement'] = $employee['departement'] ?? '—';
        $employee['poste_actuel'] = $employee['poste_actuel'] ?? '—';

        return $employee;
    }
}
