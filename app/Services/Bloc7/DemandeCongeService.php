<?php

namespace App\Services\Bloc7;

use CodeIgniter\Database\BaseConnection;
use Throwable;

/** Règles de saisie, décompte, réservation et circuit de validation des congés. */
class DemandeCongeService
{
    private BaseConnection $db;
    private DroitCongeService $droits;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->droits = new DroitCongeService($this->db);
    }

    public function liste(int $actorId, string $role, string $filter = 'all'): array
    {
        $builder = $this->db->table('demande_conge d')
            ->select('d.*, e.matricule, e.nom, e.prenom, e.manager_id, t.libelle AS type_libelle, creator.nom AS saisi_nom, creator.prenom AS saisi_prenom')
            ->join('employe e', 'e.id = d.employe_id')->join('type_conge t', 't.id = d.type_conge_id')
            ->join('employe creator', 'creator.id = d.saisi_par_employe_id');
        if ($role === 'salarie') $builder->where('d.employe_id', $actorId);
        if ($role === 'manager') $builder->where('(e.manager_id = ' . $actorId . ' OR d.employe_id = ' . $actorId . ')', null, false);
        if ($filter === 'pending') $builder->whereIn('d.statut', ['en_attente_manager', 'en_attente_rh']);
        return $builder->orderBy('d.cree_le', 'DESC')->get()->getResultArray();
    }

    public function details(int $id, int $actorId, string $role): ?array
    {
        $row = $this->db->table('demande_conge d')->select('d.*, e.matricule, e.nom, e.prenom, e.manager_id, t.libelle AS type_libelle, t.justificatif_requis, creator.nom AS saisi_nom, creator.prenom AS saisi_prenom')
            ->join('employe e', 'e.id=d.employe_id')->join('type_conge t', 't.id=d.type_conge_id')->join('employe creator', 'creator.id=d.saisi_par_employe_id')
            ->where('d.id', $id)->get()->getRowArray();
        if (!$row || ($role === 'salarie' && (int)$row['employe_id'] !== $actorId)
            || ($role === 'manager' && (int)$row['manager_id'] !== $actorId && (int)$row['employe_id'] !== $actorId)) return null;
        $history = $this->db->table('validation_demande_conge v')->select('v.*, e.nom, e.prenom')->join('employe e', 'e.id=v.validateur_employe_id')->where('v.demande_conge_id', $id);
        if ($role === 'manager') $history->where('v.etape', 'manager');
        $row['validations'] = $history->orderBy('v.decide_le')->get()->getResultArray();
        $row['justificatifs'] = ($role === 'rh' || (int)$row['employe_id'] === $actorId)
            ? $this->db->table('justificatif_conge')->where('demande_conge_id', $id)->orderBy('ajoute_le')->get()->getResultArray() : [];
        return $row;
    }

    public function options(int $actorId, string $role): array
    {
        $employees = $this->db->table('employe')->select('id, matricule, nom, prenom, manager_id')->where('actif', true);
        if ($role === 'salarie') $employees->where('id', $actorId);
        if ($role === 'manager') $employees->groupStart()->where('manager_id', $actorId)->orWhere('id', $actorId)->groupEnd();
        return ['employes' => $employees->orderBy('nom')->get()->getResultArray(),
            'types' => $this->db->table('type_conge')->where('actif', true)->orderBy('libelle')->get()->getResultArray()];
    }

    public function enregistrer(array $input, int $actorId, string $role, ?int $id = null): array
    {
        $employeeId = $role === 'salarie' ? $actorId : (int)($input['employe_id'] ?? 0);
        $employee = $this->db->table('employe')->where('id', $employeeId)->get()->getRowArray();
        if (!$employee || ($role === 'salarie' && (int)$employee['id'] !== $actorId) || ($role === 'manager' && (int)$employee['manager_id'] !== $actorId)) return $this->fail('Cet employé est hors de votre périmètre.');
        $type = $this->db->table('type_conge')->where('id', (int)($input['type_conge_id'] ?? 0))->where('actif', true)->get()->getRowArray();
        if (!$type) return $this->fail('Sélectionnez un type de congé actif.');
        $start = (string)($input['date_debut'] ?? ''); $end = (string)($input['date_fin'] ?? '');
        $portionStart = (string)($input['portion_debut'] ?? 'journee'); $portionEnd = (string)($input['portion_fin'] ?? 'journee');
        if (!$this->validDate($start) || !$this->validDate($end) || $end < $start || !in_array($portionStart, ['journee','matin','apres_midi'], true) || !in_array($portionEnd, ['journee','matin','apres_midi'], true)) return $this->fail('Vérifiez la période et les demi-journées choisies.');
        if ($start === $end && $portionStart !== $portionEnd) return $this->fail('Pour une seule date, choisissez une journée entière ou une seule demi-journée.');
        $unit='jours_ouvres';
        $duration = $this->duree($start, $end, $portionStart, $portionEnd, $unit);
        if ($duration <= 0) return $this->fail('La période ne contient aucun jour ouvré.');
        $contract = $this->contratCourant($employeeId);
        if (!$contract) return $this->fail('Aucun contrat actif ne permet de rattacher cette demande.');
        if (!empty($contract['date_fin']) && $end > $contract['date_fin']) return $this->fail('La période demandée dépasse la date de fin du contrat actif.');
        $this->droits->synchroniser($employeeId);
        $values = ['employe_id'=>$employeeId, 'saisi_par_employe_id'=>$actorId, 'contrat_id'=>(int)$contract['id'], 'type_conge_id'=>(int)$type['id'], 'date_debut'=>$start, 'date_fin'=>$end,
            'portion_debut'=>$portionStart, 'portion_fin'=>$portionEnd, 'duree_demandee'=>$duration, 'unite_decompte'=>$unit, 'motif'=>trim((string)($input['motif'] ?? ''))];
        if ($id) {
            $current = $this->db->table('demande_conge')->where('id',$id)->get()->getRowArray();
            $canEdit = $current && ((int)$current['saisi_par_employe_id'] === $actorId || ($role === 'salarie' && (int)$current['employe_id'] === $actorId));
            if (!$current || !in_array($current['statut'], ['brouillon','a_corriger'], true) || !$canEdit) return $this->fail('Seul le créateur ou le salarié concerné peut modifier un brouillon ou une demande à corriger.');
            $this->db->table('demande_conge')->where('id',$id)->update($values);
        } else {
            $values['reference'] = 'CON-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $values['statut'] = 'brouillon';
            $this->db->table('demande_conge')->insert($values); $id = (int)$this->db->insertID();
        }
        return ['ok'=>true, 'id'=>$id, 'message'=>'Brouillon enregistré.'];
    }

    public function soumettre(int $id, int $actorId): array
    {
        $this->db->transBegin();
        try {
            $d = $this->db->query('SELECT * FROM demande_conge WHERE id = ? FOR UPDATE', [$id])->getRowArray();
            if (!$d || ((int)$d['employe_id'] !== $actorId && (int)$d['saisi_par_employe_id'] !== $actorId) || !in_array($d['statut'], ['brouillon','a_corriger'], true)) throw new \RuntimeException('Cette demande ne peut pas être soumise.');
            $this->db->query('SELECT id FROM employe WHERE id=? FOR UPDATE', [$d['employe_id']])->getResultArray();
            $type = $this->db->table('type_conge')->where('id',$d['type_conge_id'])->get()->getRowArray();
            $overlap = $this->db->table('demande_conge')->where('employe_id',$d['employe_id'])->where('id <>',$id)->whereIn('statut',['en_attente_manager','en_attente_rh','approuvee'])->where('date_debut <=',$d['date_fin'])->where('date_fin >=',$d['date_debut'])->countAllResults();
            $absenceOverlap = $this->db->table('absence')->where('employe_id',$d['employe_id'])->whereIn('statut',['planifiee','en_cours'])->where('date_debut <=',$d['date_fin'])->where('date_fin >=',$d['date_debut'])->countAllResults();
            if ($overlap || $absenceOverlap) throw new \RuntimeException('La période chevauche une autre demande ou une absence existante.');
            $this->droits->synchroniser((int)$d['employe_id']);
            $type = $this->db->table('type_conge')->where('id',$d['type_conge_id'])->get()->getRowArray();
            $d['type_code'] = $type['code'];
            if ($type['code'] === 'ANNUEL') {
                $balance = $this->db->table('solde_conge')->where(['contrat_id'=>$d['contrat_id'],'employe_id'=>$d['employe_id'],'type_conge_id'=>$d['type_conge_id']])->get()->getRowArray();
                if (!$balance || (float)$balance['quantite_disponible'] < (float)$d['duree_demandee']) throw new \RuntimeException('Le solde annuel déjà acquis et non expiré est insuffisant.');
                $attempt = (int)($this->db->table('validation_demande_conge')->selectMax('numero_tentative')->where('demande_conge_id',$id)->get()->getRowArray()['numero_tentative'] ?? 0) + 1;
                $this->droits->reserver($d, $attempt, date('Y-m-d'));
            }
            $managerId = $this->db->table('employe')->select('manager_id')->where('id',$d['employe_id'])->get()->getRowArray()['manager_id'] ?? null;
            if ($managerId !== null && (int)$managerId === (int)$d['employe_id']) $managerId = null;
            $status = $managerId ? 'en_attente_manager' : 'en_attente_rh';
            $this->db->table('demande_conge')->where('id',$id)->update(['statut'=>$status,'soumise_le'=>date('Y-m-d H:i:s')]);
            if (!$this->db->transStatus()) throw new \RuntimeException('La demande n’a pas pu être soumise.');
            if ($managerId) {
                $this->notifier((int)$managerId, $id, 'demande_soumise', 'Nouvelle demande de congé', 'Une demande attend votre décision.');
            } else {
                $rhIds = $this->db->table('employe')->select('employe.id')->join('role','role.id=employe.role_id')
                    ->where('role.nom_role','RH')->where('employe.actif',true)->get()->getResultArray();
                foreach ($rhIds as $rh) $this->notifier((int)$rh['id'], $id, 'demande_soumise', 'Nouvelle demande de congé', 'Une demande sans manager attend une décision RH.');
            }
            $this->notifier((int)$d['employe_id'], $id, 'demande_envoyee', 'Demande transmise', 'Votre demande a été transmise au valideur.');
            $this->db->transCommit(); return ['ok'=>true,'message'=>'Demande soumise pour validation.'];
        } catch (Throwable $e) { $this->db->transRollback(); return $this->fail($e->getMessage()); }
    }

    public function decider(int $id, int $actorId, string $role, string $decision, string $comment): array
    {
        if (!in_array($decision,['approuvee','refusee','a_corriger'],true)) return $this->fail('Décision invalide.');
        $this->db->transBegin();
        try {
            $d = $this->db->query('SELECT d.*, e.manager_id FROM demande_conge d JOIN employe e ON e.id=d.employe_id WHERE d.id=? FOR UPDATE OF d',[$id])->getRowArray();
            if (!$d) throw new \RuntimeException('Demande introuvable.');
            $step = $d['statut'] === 'en_attente_manager' ? 'manager' : ($d['statut'] === 'en_attente_rh' ? 'rh' : '');
            if (!$step || ($step === 'manager' && ($role !== 'manager' || (int)$d['manager_id'] !== $actorId)) || ($step === 'rh' && !in_array($role,['rh','admin'],true))) throw new \RuntimeException('Vous ne pouvez pas décider de cette demande.');
            if (in_array($decision,['refusee','a_corriger'],true) && trim($comment)==='') throw new \RuntimeException('Ajoutez un commentaire pour un refus ou un retour en correction.');
            $attempt = (int)($this->db->table('validation_demande_conge')->selectMax('numero_tentative')->where('demande_conge_id',$id)->where('etape',$step)->get()->getRowArray()['numero_tentative'] ?? 0) + 1;
            $this->db->table('validation_demande_conge')->insert(['demande_conge_id'=>$id,'validateur_employe_id'=>$actorId,'etape'=>$step,'numero_tentative'=>$attempt,'decision'=>$decision,'commentaire'=>trim($comment)]);
            if ($decision === 'a_corriger' || $decision === 'refusee') {
                $this->libererReservation($d);
                $this->db->table('demande_conge')->where('id',$id)->update(['statut'=>$decision,'cloturee_le'=>$decision === 'refusee' ? date('Y-m-d H:i:s') : null]);
            } else {
                $type = $this->db->table('type_conge')->where('id',$d['type_conge_id'])->get()->getRowArray();
                if ($type['code'] === 'ANNUEL') $this->droits->finaliserAllocations($id, 'approuvee');
                $this->db->table('absence')->insert(['demande_conge_id'=>$id,'employe_id'=>$d['employe_id'],'contrat_id'=>$d['contrat_id'],'type_conge_id'=>$d['type_conge_id'],'date_debut'=>$d['date_debut'],'date_fin'=>$d['date_fin'],'duree'=>$d['duree_demandee'],'unite_decompte'=>'jours_ouvres','statut'=>'planifiee']);
                $this->db->table('demande_conge')->where('id',$id)->update(['statut'=>'approuvee','cloturee_le'=>date('Y-m-d H:i:s')]);
            }
            $this->droits->recalculer((int)$d['contrat_id'],(int)$d['employe_id'],(int)$d['type_conge_id']);
            $this->notifier((int)$d['employe_id'],$id,'decision_'.$decision,'Mise à jour de votre demande',match($decision){'approuvee'=>'Votre demande a été approuvée.','refusee'=>'Votre demande a été refusée.','a_corriger'=>'Votre demande nécessite une correction.'});
            if (!$this->db->transStatus()) throw new \RuntimeException('La décision n’a pas pu être enregistrée.');
            $this->db->transCommit(); return ['ok'=>true,'message'=>$decision === 'approuvee' ? 'Décision approuvée.' : ($decision === 'a_corriger' ? 'Demande renvoyée pour correction.' : 'Demande refusée.')];
        } catch (Throwable $e) { $this->db->transRollback(); return $this->fail($e->getMessage()); }
    }

    public function retirer(int $id, int $actorId, string $role, string $motif): array
    {
        $this->db->transBegin();
        try {
            $d = $this->db->query('SELECT * FROM demande_conge WHERE id=? FOR UPDATE',[$id])->getRowArray();
            if (!$d) throw new \RuntimeException('Demande introuvable.');
            $employeeAction = (int)$d['employe_id'] === $actorId && in_array($d['statut'],['brouillon','a_corriger','en_attente_manager','en_attente_rh'],true);
            if ($employeeAction) {
                if ((int)$d['employe_id'] !== $actorId || !in_array($d['statut'],['brouillon','a_corriger','en_attente_manager','en_attente_rh'],true)) throw new \RuntimeException('Vous ne pouvez retirer que votre demande non approuvée.');
                $this->libererReservation($d);
                $this->db->table('demande_conge')->where('id',$id)->update(['statut'=>'retiree','cloturee_le'=>date('Y-m-d H:i:s')]);
                if (!$this->db->transStatus()) throw new \RuntimeException('Le retrait a échoué. Vérifiez que le script SQL Bloc 7C à jour a été appliqué.');
                $this->db->table('annulation_demande_conge')->insert(['demande_conge_id'=>$id,'annule_par_employe_id'=>$actorId,'motif'=>trim($motif) ?: 'Retrait par le salarié avant approbation.']);
            } else {
                if (!in_array($role,['rh','admin'],true) || $d['statut'] !== 'approuvee') throw new \RuntimeException('Seule la RH peut annuler une demande approuvée.');
                $this->droits->restituer($id);
                $this->db->table('absence')->where('demande_conge_id',$id)->update(['statut'=>'annulee']);
                $this->db->table('demande_conge')->where('id',$id)->update(['statut'=>'annulee','cloturee_le'=>date('Y-m-d H:i:s')]);
                if (!$this->db->transStatus()) throw new \RuntimeException('L’annulation a échoué.');
                $this->db->table('annulation_demande_conge')->insert(['demande_conge_id'=>$id,'annule_par_employe_id'=>$actorId,'motif'=>trim($motif) ?: 'Annulation RH après approbation.']);
            }
            $this->droits->recalculer((int)$d['contrat_id'],(int)$d['employe_id'],(int)$d['type_conge_id']);
            $this->notifier((int)$d['employe_id'],$id,'demande_retiree','Demande retirée','La demande a été retirée ou annulée.');
            if (!$this->db->transStatus()) throw new \RuntimeException('L’annulation n’a pas pu être enregistrée.');
            $this->db->transCommit(); return ['ok'=>true,'message'=>'Demande annulée et solde restitué.'];
        } catch (Throwable $e) { $this->db->transRollback(); return $this->fail($e->getMessage()); }
    }

    public function justificatifs(int $id): array { return $this->db->table('justificatif_conge')->where('demande_conge_id',$id)->get()->getResultArray(); }

    private function soldeCorrespondant(array $d, bool $lock = false): ?array
    {
        $q=$this->db->table('solde_conge')->where(['contrat_id'=>$d['contrat_id'],'employe_id'=>$d['employe_id'],'type_conge_id'=>$d['type_conge_id']]);
        return $q->get()->getRowArray();
    }
    private function libererReservation(array $d): void
    {
        $this->droits->finaliserAllocations((int)$d['id'],'liberee');
        $this->droits->recalculer((int)$d['contrat_id'],(int)$d['employe_id'],(int)$d['type_conge_id']);
    }

    private function contratCourant(int $employeeId): ?array
    {
        return $this->db->table('contrat')->where('employe_id',$employeeId)
            ->whereIn('statut',['signe','en_cours'])->where('date_debut <=',date('Y-m-d'))
            ->groupStart()->where('date_fin >=',date('Y-m-d'))->orWhere('date_fin IS NULL',null,false)->groupEnd()
            ->orderBy('date_debut','DESC')->get()->getRowArray();
    }

    private function notifier(int $employeeId,int $requestId,string $code,string $title,string $message): void
    {
        $this->db->table('notification_employe')->insert(['destinataire_employe_id'=>$employeeId,'demande_conge_id'=>$requestId,'code'=>$code,'titre'=>$title,'message'=>$message]);
    }
    private function duree(string $start,string $end,string $pStart,string $pEnd,string $unit): float
    {
        $holidays=array_column($this->db->table('jour_ferie')->select('date_ferie')->where('actif',true)->where('date_ferie >=',$start)->where('date_ferie <=',$end)->get()->getResultArray(),'date_ferie');
        $days=0.0; $cursor=new \DateTimeImmutable($start); $last=new \DateTimeImmutable($end); $working=[];
        while ($cursor <= $last) { $date=$cursor->format('Y-m-d'); if ((int)$cursor->format('N')<6 && !in_array($date,$holidays,true)) { $days += 1; $working[$date]=true; } $cursor=$cursor->modify('+1 day'); }
        if ($start !== $end) { if ($pStart !== 'journee' && isset($working[$start])) $days -= .5; if ($pEnd !== 'journee' && isset($working[$end])) $days -= .5; }
        elseif ($pStart !== 'journee') $days = $days > 0 ? .5 : 0;
        return max(0,$days);
    }
    private function validDate(string $date): bool { $d=\DateTime::createFromFormat('Y-m-d',$date); return $d && $d->format('Y-m-d')===$date; }
    private function fail(string $message): array { return ['ok'=>false,'message'=>$message]; }
}
