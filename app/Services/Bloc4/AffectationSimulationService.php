<?php

namespace App\Services\Bloc4;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use Throwable;

/** Affectation provisoire du Bloc 4, en attente du service partagé du Bloc 3. */
class AffectationSimulationService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function pageData(): array
    {
        $employes = $this->db->table('employe')->select('id, matricule, nom, prenom')->orderBy('nom')->get()->getResultArray();
        $postes = $this->db->table('poste p')->select('p.id, p.intitule, d.nom_departement')
            ->join('departement d', 'd.id = p.departement_id')
            ->join('status s', 's.id = p.status_id')->where('s.code', 'VACANT')
            ->orderBy('p.intitule')->get()->getResultArray();
        $affectations = $this->db->table('affectation a')
            ->select('a.id, a.employe_id, a.poste_id, a.date_debut, a.date_fin, e.matricule, e.nom, e.prenom, p.intitule')
            ->join('employe e', 'e.id = a.employe_id')->join('poste p', 'p.id = a.poste_id')
            ->orderBy('a.date_debut', 'DESC')->get()->getResultArray();

        $activeEmployees = array_column(array_filter($affectations, static fn ($row) => $row['date_fin'] === null), 'employe_id');
        $activePositions = array_column(array_filter($affectations, static fn ($row) => $row['date_fin'] === null), 'poste_id');
        return [
            'employes' => $employes,
            'postes' => $postes,
            'employesLibres' => array_values(array_filter($employes, static fn ($row) => ! in_array($row['id'], $activeEmployees))),
            'postesLibres' => array_values(array_filter($postes, static fn ($row) => ! in_array($row['id'], $activePositions))),
            'affectations' => $affectations,
        ];
    }

    public function enregistrer(array $input): array
    {
        $employeeId = (int) ($input['employe_id'] ?? 0);
        $positionId = (int) ($input['poste_id'] ?? 0);
        $startValue = (string) ($input['date_debut'] ?? '');
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $startValue);
        $errors = DateTimeImmutable::getLastErrors();
        if ($start === false || ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) || $start->format('Y-m-d') !== $startValue) {
            return ['ok' => false, 'message' => 'Saisissez une date de début valide.'];
        }
        if (! $this->db->table('employe')->where('id', $employeeId)->countAllResults()) return ['ok' => false, 'message' => 'Sélectionnez un employé existant.'];
        $vacantPosition = $this->db->table('poste p')->select('p.id')->join('status s', 's.id = p.status_id')
            ->where('p.id', $positionId)->where('s.code', 'VACANT')->get()->getRowArray();
        if ($vacantPosition === null) return ['ok' => false, 'message' => 'Sélectionnez un poste vacant.'];
        if ($this->db->table('affectation')->where('employe_id', $employeeId)->where('date_fin IS NULL', null, false)->countAllResults()) {
            return ['ok' => false, 'message' => 'Cet employé possède déjà une affectation active. Clôturez-la avant d’en créer une autre.'];
        }
        if ($this->db->table('affectation')->where('poste_id', $positionId)->where('date_fin IS NULL', null, false)->countAllResults()) {
            return ['ok' => false, 'message' => 'Ce poste est déjà occupé par un titulaire actif.'];
        }
        try {
            $this->db->transBegin();
            $ok = $this->db->table('affectation')->insert([
                'employe_id' => $employeeId,
                'poste_id' => $positionId,
                'date_debut' => $startValue,
                'date_fin' => null,
                'cree_par' => null,
            ]);
            if (! $ok) throw new \RuntimeException('L’affectation n’a pas pu être enregistrée.');
            $filledStatus = $this->db->table('status')->select('id')->where('code', 'POURVU')->get()->getRowArray();
            if ($filledStatus === null) throw new \RuntimeException('Le statut POURVU n’est pas configuré.');
            if (! $this->db->table('poste')->where('id', $positionId)->update(['status_id' => $filledStatus['id']])) {
                throw new \RuntimeException('Le statut du poste n’a pas pu être mis à jour.');
            }
            $this->db->transCommit();
            return ['ok' => true, 'message' => 'Affectation de simulation enregistrée en base.'];
        } catch (Throwable $e) {
            $this->db->transRollback();
            return ['ok' => false, 'message' => 'L’affectation de simulation n’a pas été enregistrée. Vérifiez que l’employé et le poste n’ont pas déjà une affectation active.'];
        }
    }
}
