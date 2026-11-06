<?php

namespace App\Services\Bloc5;

use App\Models\EmployeModel;

class DossierService
{
    private EmployeModel $employees;

    public function __construct(?EmployeModel $employees = null)
    {
        $this->employees = $employees ?? model(EmployeModel::class);
    }

    public function page(): array
    {
        return [
            'slug' => 'dossier',
            'title' => 'Dossier administratif',
            'subtitle' => 'Identité, coordonnées et situation professionnelle',
            'description' => 'Vue synthétique d’un dossier salarié, avec ses contrats, affectations, salaire et historique de carrière.',
            'stats' => [
                ['Contrat', 'CDI'],
                ['Ancienneté', '3 ans'],
                ['Affectation', 'Administration'],
            ],
            'fields' => [
                ['Identité', 'Nom, prénom, date de naissance, matricule'],
                ['Coordonnées', 'Adresse, téléphone, e-mail'],
                ['Situation professionnelle', 'Département, poste, responsable, date d’embauche'],
                ['Rémunération', 'Salaire de référence · accès restreint'],
            ],
            'columns' => ['Événement de carrière', 'Poste / situation', 'Date d’effet'],
            'rows' => [
                ['Embauche', 'Assistant administratif', '01/03/2023'],
                ['Changement de poste', 'Responsable RH', '01/06/2025'],
            ],
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

    private function normalizeEmployee(array $employee): array
    {
        $employee['statut'] = ((int) ($employee['actif'] ?? 0)) === 1 ? 'Actif' : 'Inactif';
        $employee['date_embauche'] = self::formatDate((string) ($employee['date_embauche'] ?? ''));
        $employee['date_naissance'] = self::formatDate((string) ($employee['date_naissance'] ?? ''));
        $employee['date_delivrance_cni'] = self::formatDate((string) ($employee['date_delivrance_cni'] ?? ''));
        $employee['photo'] = ! empty($employee['photo']) ? (string) $employee['photo'] : 'assets/images/default-avatar.png';
        $employee['departement'] = $employee['departement'] ?? '—';
        $employee['poste_actuel'] = $employee['poste_actuel'] ?? '—';
        $employee['telephone'] = $employee['telephone'] ?? '—';
        $employee['email'] = $employee['email'] ?? '—';
        $employee['adresse'] = $employee['adresse'] ?? '—';
        $employee['situation_familiale'] = $employee['situation_familiale'] ?? '—';
        $employee['numero_cni'] = $employee['numero_cni'] ?? '—';
        $employee['lieu_delivrance_cni'] = $employee['lieu_delivrance_cni'] ?? '—';

        return $employee;
    }

    private static function formatDate(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '—';
        }

        $date = date_create_from_format('Y-m-d', $value) ?: date_create($value);

        if ($date === false) {
            return $value;
        }

        return $date->format('d/m/Y');
    }
}
