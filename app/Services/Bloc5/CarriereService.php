<?php

namespace App\Services\Bloc5;

use CodeIgniter\Database\BaseConnection;

class CarriereService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function employee(int $employeeId): ?array
    {
        $employee = $this->db->table('employe e')
            ->select('e.id, e.matricule, e.nom, e.prenom, e.email, e.date_embauche, e.actif, d.nom_departement AS departement')
            ->join('departement d', 'd.id = e.departement_id', 'left')
            ->where('e.id', $employeeId)
            ->get()
            ->getRowArray();

        if ($employee === null) {
            return null;
        }

        $employee['nom_complet'] = trim(($employee['prenom'] ?? '') . ' ' . ($employee['nom'] ?? ''));
        $employee['statut'] = ((bool) ($employee['actif'] ?? false)) ? 'Actif' : 'Inactif';

        return $employee;
    }

    public function searchEmployees(string $term): array
    {
        $search = trim($term);
        if ($search === '') {
            return [];
        }

        $like = '%' . strtolower($search) . '%';

        return $this->db->table('employe e')
            ->select('e.id, e.matricule, e.nom, e.prenom, e.actif')
            ->where('e.actif', true)
            ->groupStart()
                ->like('LOWER(e.matricule)', $like)
                ->orLike('LOWER(e.nom)', $like)
                ->orLike('LOWER(e.prenom)', $like)
            ->groupEnd()
            ->orderBy('e.nom', 'ASC')
            ->orderBy('e.prenom', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function validateDateRange(?string $dateDebut = null, ?string $dateFin = null): array
    {
        $start = trim((string) ($dateDebut ?? ''));
        $end = trim((string) ($dateFin ?? ''));

        if ($start !== '' && ! $this->isValidDate($start)) {
            return ['ok' => false, 'message' => 'La date de début est invalide.', 'start' => '', 'end' => $end];
        }

        if ($end !== '' && ! $this->isValidDate($end)) {
            return ['ok' => false, 'message' => 'La date de fin est invalide.', 'start' => $start, 'end' => ''];
        }

        if ($start !== '' && $end !== '' && $start > $end) {
            return ['ok' => false, 'message' => 'La date de début doit être antérieure ou égale à la date de fin.', 'start' => $start, 'end' => $end];
        }

        return ['ok' => true, 'message' => '', 'start' => $start, 'end' => $end];
    }

    public function contractsForEmployee(int $employeeId, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $builder = $this->db->table('contrat c')
            ->select('c.id, c.salaire, c.date_debut, c.date_fin, c.date_signature, c.statut, tc.code AS type_code, tc.libelle AS type_libelle')
            ->join('type_contrat tc', 'tc.id = c.type_contrat_id', 'left')
            ->where('c.employe_id', $employeeId)
            ->orderBy('c.date_debut', 'DESC');

        if ($dateDebut !== null && $dateDebut !== '') {
            $builder->where('c.date_debut >=', $dateDebut);
        }

        if ($dateFin !== null && $dateFin !== '') {
            $builder->groupStart()
                ->where('c.date_debut <=', $dateFin)
                ->orWhere('c.date_fin IS NULL')
                ->orWhere('c.date_fin <=', $dateFin)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function affectationsForEmployee(int $employeeId, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $builder = $this->db->table('affectation a')
            ->select('a.id, a.date_debut, a.date_fin, p.intitule AS poste, d.nom_departement AS departement')
            ->join('poste p', 'p.id = a.poste_id', 'left')
            ->join('departement d', 'd.id = p.departement_id', 'left')
            ->where('a.employe_id', $employeeId)
            ->orderBy('a.date_debut', 'DESC');

        if ($dateDebut !== null && $dateDebut !== '') {
            $builder->where('a.date_debut >=', $dateDebut);
        }

        if ($dateFin !== null && $dateFin !== '') {
            $builder->groupStart()
                ->where('a.date_debut <=', $dateFin)
                ->orWhere('a.date_fin IS NULL')
                ->orWhere('a.date_fin <=', $dateFin)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function historiqueSalaireForEmployee(int $employeeId, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $builder = $this->db->table('historique_salaire hs')
            ->select('hs.id, hs.ancien_salaire, hs.nouveau_salaire, hs.date_effet, hs.motif')
            ->where('hs.employe_id', $employeeId)
            ->orderBy('hs.date_effet', 'DESC');

        if ($dateDebut !== null && $dateDebut !== '') {
            $builder->where('hs.date_effet >=', $dateDebut);
        }

        if ($dateFin !== null && $dateFin !== '') {
            $builder->where('hs.date_effet <=', $dateFin);
        }

        return $builder->get()->getResultArray();
    }

    private function isValidDate(string $date): bool
    {
        $d = date_create_from_format('Y-m-d', $date);

        return $d instanceof \DateTimeInterface && $d->format('Y-m-d') === $date;
    }
}
