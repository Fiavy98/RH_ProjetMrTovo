<?php

namespace App\Services\Bloc6;

use App\Models\Bloc6\AnomalieDModel;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

class AnomaliesService
{
    private BaseConnection $db;
    private AnomalieDModel $model;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->model = new AnomalieDModel($this->db);
    }

    public function detect(string $from, string $to, ?DateTimeImmutable $now = null): int
    {
        $this->assertPeriod($from, $to);
        $now ??= new DateTimeImmutable('now', new DateTimeZone('Indian/Antananarivo'));
        $plans = $this->db->table('planning_travail')->where('date_travail >=', $from)->where('date_travail <=', $to)->get()->getResultArray();
        $punches = $this->db->table('pointage')->where('date_pointage >=', $from)->where('date_pointage <=', $to)->get()->getResultArray();
        $byKey = [];
        foreach ($plans as $plan) {
            $key = $plan['employe_id'] . '|' . substr((string) $plan['date_travail'], 0, 10);
            $byKey[$key]['planning'] = $plan;
        }
        foreach ($punches as $punch) {
            $key = $punch['employe_id'] . '|' . substr((string) $punch['date_pointage'], 0, 10);
            $byKey[$key]['pointage'] = $punch;
        }

        $created = 0;
        foreach ($byKey as $key => $pair) {
            [$employeeId, $date] = explode('|', $key);
            foreach (AnomalyRules::detect($pair['planning'] ?? null, $pair['pointage'] ?? null, $now) as $anomaly) {
                $existing = $this->model->where(['employe_id' => $employeeId, 'date_anomalie' => $date, 'type' => $anomaly['type']])->first();
                $payload = [
                    'planning_id' => $pair['planning']['id'] ?? null,
                    'pointage_id' => $pair['pointage']['id'] ?? null,
                    'detail' => $anomaly['detail'],
                    'derniere_detection_le' => $now->format('Y-m-d H:i:s'),
                ];
                if ($existing) {
                    $this->model->update($existing['id'], $payload);
                    continue;
                }
                $this->model->insert($payload + [
                    'employe_id' => (int) $employeeId,
                    'date_anomalie' => $date,
                    'type' => $anomaly['type'],
                    'statut' => 'A_EXAMINER',
                ]);
                $created++;
            }
        }
        return $created;
    }

    public function listing(array $filters): array
    {
        $builder = $this->db->table('anomalie_pointage a')
            ->select('a.*, e.matricule, e.nom, e.prenom, d.nom_departement')
            ->join('employe e', 'e.id = a.employe_id')
            ->join('departement d', 'd.id = e.departement_id', 'left');
        if (! empty($filters['date_debut'])) $builder->where('a.date_anomalie >=', $filters['date_debut']);
        if (! empty($filters['date_fin'])) $builder->where('a.date_anomalie <=', $filters['date_fin']);
        if (! empty($filters['employe_id'])) $builder->where('a.employe_id', (int) $filters['employe_id']);
        if (! empty($filters['departement_id'])) $builder->where('e.departement_id', (int) $filters['departement_id']);
        if (! empty($filters['type'])) $builder->where('a.type', $filters['type']);
        if (! empty($filters['statut'])) $builder->where('a.statut', $filters['statut']);
        return $builder->orderBy('a.date_anomalie', 'DESC')->orderBy('a.id', 'DESC')->get()->getResultArray();
    }

    public function detail(int $id): ?array
    {
        $row = $this->db->table('anomalie_pointage a')->select('a.*, e.matricule, e.nom, e.prenom, d.nom_departement, u.nom AS auteur_nom, u.prenom AS auteur_prenom')
            ->join('employe e', 'e.id = a.employe_id')->join('departement d', 'd.id = e.departement_id', 'left')
            ->join('employe u', 'u.id = a.traitee_par', 'left')->where('a.id', $id)->get()->getRowArray();
        if (! $row) return null;
        $row['historique'] = $this->db->table('anomalie_pointage_historique h')->select('h.*, e.nom, e.prenom')
            ->join('employe e', 'e.id = h.auteur_id', 'left')->where('h.anomalie_id', $id)->orderBy('h.cree_le', 'DESC')->get()->getResultArray();
        return $row;
    }

    public function treat(int $id, string $status, string $reason, int $authorId): void
    {
        AnomalyTreatmentRules::assertValid($status, $reason);
        $current = $this->model->find($id);
        if (! $current) throw new InvalidArgumentException('Anomalie introuvable.');
        $this->db->transStart();
        $this->model->update($id, [
            'statut' => $status,
            'motif_traitement' => trim($reason) ?: null,
            'traitee_le' => date('Y-m-d H:i:s'),
            'traitee_par' => $authorId,
        ]);
        $this->db->table('anomalie_pointage_historique')->insert([
            'anomalie_id' => $id, 'ancien_statut' => $current['statut'], 'nouveau_statut' => $status,
            'motif' => trim($reason) ?: null, 'auteur_id' => $authorId,
        ]);
        $this->db->transComplete();
        if (! $this->db->transStatus()) throw new \RuntimeException('Le traitement n’a pas pu être enregistré.');
    }

    public function stats(array $rows): array
    {
        $stats = ['A_EXAMINER' => 0, 'EN_COURS' => 0, 'REGULARISEE' => 0, 'IGNOREE' => 0];
        foreach ($rows as $row) $stats[$row['statut']] = ($stats[$row['statut']] ?? 0) + 1;
        return $stats;
    }

    public function options(): array
    {
        return [
            'employes' => $this->db->table('employe')->select('id, matricule, nom, prenom')->where('actif', true)->orderBy('nom')->get()->getResultArray(),
            'departements' => $this->db->table('departement')->select('id, nom_departement')->orderBy('nom_departement')->get()->getResultArray(),
        ];
    }

    private function assertPeriod(string $from, string $to): void
    {
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $to);
        if (! $start || ! $end || $end < $start || $end->diff($start)->days > 366) throw new InvalidArgumentException('Période invalide (maximum 366 jours).');
    }
}
