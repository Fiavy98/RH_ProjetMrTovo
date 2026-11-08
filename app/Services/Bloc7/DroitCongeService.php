<?php

namespace App\Services\Bloc7;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use RuntimeException;

/** Acquisition mensuelle, expiration, réservation et consommation FIFO des droits. */
class DroitCongeService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /** Crée les tranches dues pour les contrats salariés et recalcule les soldes. */
    public function synchroniser(int $employeeId, ?string $today = null): void
    {
        $today ??= date('Y-m-d');
        $contracts = $this->db->table('contrat')->where('employe_id', $employeeId)
            ->whereIn('statut', ['signe', 'en_cours', 'termine'])->orderBy('date_debut')->get()->getResultArray();
        $annual = $this->db->table('type_conge')->where('code', 'ANNUEL')->get()->getRowArray();
        if (!$annual) return;

        foreach ($contracts as $contract) {
            $start = new DateTimeImmutable($contract['date_debut']);
            $end = new DateTimeImmutable(min($today, $contract['date_fin'] ?: $today));
            $i = 1;
            while (($acquired = $this->anniversary($start, $i)) <= $end) {
                $expires = $this->anniversary($acquired, 36)->format('Y-m-d');
                $day = $acquired->format('Y-m-d');
                $this->db->query("INSERT INTO droit_conge
                    (employe_id, contrat_id, type_conge_id, date_acquisition, date_expiration, quantite, nature, origine)
                    VALUES (?, ?, ?, ?, ?, 2.5, 'acquisition', 'Acquisition mensuelle automatique')
                    ON CONFLICT (contrat_id, type_conge_id, date_acquisition) WHERE nature='acquisition' DO NOTHING",
                    [$employeeId, $contract['id'], $annual['id'], $day, $expires]);
                $i++;
            }
            $this->recalculer((int) $contract['id'], $employeeId, (int) $annual['id'], $today);
        }
    }

    /** Réserve les droits annuels expirant le plus tôt en premier. */
    public function reserver(array $request, int $tentative, string $today): void
    {
        if ($request['type_code'] !== 'ANNUEL') return;
        // Verrouille les mêmes tranches entre soumissions concurrentes.
        $this->db->query('SELECT id FROM droit_conge WHERE contrat_id=? AND type_conge_id=? AND date_expiration>? FOR UPDATE',
            [$request['contrat_id'],$request['type_conge_id'],$today])->getResultArray();
        $rows = $this->tranchesDisponibles((int)$request['contrat_id'], (int)$request['type_conge_id'], $today);
        $need = (float)$request['duree_demandee'];
        foreach ($rows as $row) {
            if ($need <= 0.0001) break;
            $allocated = (float)$row['allocated'];
            $available = max(0, (float)$row['quantite'] - $allocated);
            $take = min($need, $available);
            if ($take <= 0) continue;
            $this->db->table('allocation_droit_conge')->insert([
                'demande_conge_id' => $request['id'], 'droit_conge_id' => $row['id'],
                'numero_tentative' => $tentative, 'quantite' => $take, 'statut' => 'reservee',
            ]);
            $need -= $take;
        }
        if ($need > 0.0001) throw new RuntimeException('Le solde annuel acquis et non expiré est insuffisant.');
        $this->recalculer((int)$request['contrat_id'], (int)$request['employe_id'], (int)$request['type_conge_id'], $today);
    }

    public function finaliserAllocations(int $requestId, string $decision): void
    {
        $status = $decision === 'approuvee' ? 'consommee' : 'liberee';
        $this->db->table('allocation_droit_conge')->where('demande_conge_id', $requestId)
            ->where('statut', 'reservee')->update(['statut' => $status]);
    }

    public function restituer(int $requestId): void
    {
        $this->db->table('allocation_droit_conge')->where('demande_conge_id', $requestId)
            ->where('statut', 'consommee')->update(['statut' => 'liberee']);
    }

    public function recalculer(int $contractId, int $employeeId, int $typeId, ?string $today = null): void
    {
        $today ??= date('Y-m-d');
        $type = $this->db->table('type_conge')->select('code')->where('id',$typeId)->get()->getRowArray();
        if (!$type || $type['code'] !== 'ANNUEL') return;
        $acquired = (float)($this->db->table('droit_conge')->selectSum('quantite', 'total')
            ->where(['contrat_id'=>$contractId,'employe_id'=>$employeeId,'type_conge_id'=>$typeId])
            ->where('date_expiration >', $today)->get()->getRowArray()['total'] ?? 0);
        $consumed = (float)($this->db->table('allocation_droit_conge a')->selectSum('a.quantite','total')
            ->join('droit_conge d','d.id=a.droit_conge_id')
            ->where(['d.contrat_id'=>$contractId,'d.employe_id'=>$employeeId,'d.type_conge_id'=>$typeId,'a.statut'=>'consommee'])
            ->where('d.date_expiration >',$today)->get()->getRowArray()['total'] ?? 0);
        $reserved = (float)($this->db->table('allocation_droit_conge a')->selectSum('a.quantite','total')
            ->join('droit_conge d','d.id=a.droit_conge_id')
            ->where(['d.contrat_id'=>$contractId,'d.employe_id'=>$employeeId,'d.type_conge_id'=>$typeId,'a.statut'=>'reservee'])
            ->where('d.date_expiration >',$today)->get()->getRowArray()['total'] ?? 0);
        $existing = $this->db->table('solde_conge')->where(['contrat_id'=>$contractId,'type_conge_id'=>$typeId])->get()->getRowArray();
        $values = ['quantite_acquise'=>$acquired,'quantite_consommee'=>$consumed,'quantite_reservee'=>$reserved,'calcule_le'=>date('Y-m-d H:i:s')];
        if ($existing) $this->db->table('solde_conge')->where('id',$existing['id'])->update($values);
        else $this->db->table('solde_conge')->insert($values + ['employe_id'=>$employeeId,'contrat_id'=>$contractId,'type_conge_id'=>$typeId]);
    }

    private function tranchesDisponibles(int $contractId, int $typeId, string $today): array
    {
        return $this->db->query("SELECT d.*, COALESCE(SUM(CASE WHEN a.statut IN ('reservee','consommee') THEN a.quantite ELSE 0 END),0) AS allocated
            FROM droit_conge d LEFT JOIN allocation_droit_conge a ON a.droit_conge_id=d.id
            WHERE d.contrat_id=? AND d.type_conge_id=? AND d.date_acquisition<=? AND d.date_expiration>?
            GROUP BY d.id ORDER BY d.date_expiration, d.date_acquisition, d.id", [$contractId,$typeId,$today,$today])->getResultArray();
    }

    private function anniversary(DateTimeImmutable $start, int $months): DateTimeImmutable
    {
        $monthIndex = ((int)$start->format('Y')) * 12 + (int)$start->format('n') - 1 + $months;
        $year = intdiv($monthIndex, 12); $month = ($monthIndex % 12) + 1;
        $day = min((int)$start->format('j'), cal_days_in_month(CAL_GREGORIAN, $month, $year));
        return $start->setDate($year, $month, $day);
    }
}
