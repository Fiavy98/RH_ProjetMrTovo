<?php

namespace App\Services\Bloc4;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use Throwable;

/** Gestion de l'onboarding et sauvegarde groupée des six tâches. */
class OnboardingService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function liste(array $filters = []): array
    {
        $builder = $this->db->table('onboarding o')
            ->select("o.id, o.employe_id, o.statut, o.cree_le, e.matricule, e.nom, e.prenom, COUNT(t.id) AS total_taches, SUM(CASE WHEN t.statut = 'termine' THEN 1 ELSE 0 END) AS taches_terminees")
            ->join('employe e', 'e.id = o.employe_id')
            ->join('onboarding_tache t', 't.onboarding_id = o.id', 'left')
            ->groupBy('o.id, e.matricule, e.nom, e.prenom')
            ->orderBy('o.cree_le', 'DESC');
        if (! empty($filters['statut'])) $builder->where('o.statut', $filters['statut']);
        return $builder->get()->getResultArray();
    }

    public function detail(int $id): ?array
    {
        $onboarding = $this->db->table('onboarding o')
            ->select('o.*, e.matricule, e.nom, e.prenom')
            ->join('employe e', 'e.id = o.employe_id')
            ->where('o.id', $id)->get()->getRowArray();
        if ($onboarding === null) return null;

        $onboarding['taches'] = $this->db->table('onboarding_tache t')
            ->select('t.id, t.type_tache_id, t.responsable_id, t.echeance, t.statut, t.date_realisation, tt.libelle, tt.ordre')
            ->join('type_tache_onboarding tt', 'tt.id = t.type_tache_id')
            ->where('t.onboarding_id', $id)->orderBy('tt.ordre')->get()->getResultArray();
        $onboarding['responsables'] = $this->db->table('employe')->select('id, matricule, nom, prenom')->orderBy('nom')->get()->getResultArray();
        return $onboarding;
    }

    /** @param array<string, mixed> $input */
    public function enregistrerTaches(int $id, array $input): array
    {
        $onboarding = $this->detail($id);
        if ($onboarding === null) return ['ok' => false, 'message' => 'Onboarding introuvable.'];
        $tasks = $onboarding['taches'];
        if (count($tasks) !== 6) return ['ok' => false, 'message' => 'L’onboarding doit comporter exactement six tâches.'];

        $statuses = $input['statut'] ?? [];
        $dueDates = $input['echeance'] ?? [];
        $responsibles = $input['responsable_id'] ?? [];
        $completionDates = $input['date_realisation'] ?? [];
        $allowed = ['a_faire', 'en_cours', 'termine'];
        $taskIds = array_map(static fn ($task) => (string) $task['id'], $tasks);
        if (count($statuses) !== 6 || array_diff($taskIds, array_map('strval', array_keys($statuses))) !== []) {
            return ['ok' => false, 'message' => 'Transmettez le statut des six tâches avant d’enregistrer.'];
        }

        $updates = [];
        foreach ($tasks as $task) {
            $taskId = (string) $task['id'];
            $status = (string) ($statuses[$taskId] ?? '');
            if (! in_array($status, $allowed, true)) return ['ok' => false, 'message' => 'Statut de tâche invalide.'];

            $due = $this->optionalDate((string) ($dueDates[$taskId] ?? ''));
            if (($dueDates[$taskId] ?? '') !== '' && $due === null) return ['ok' => false, 'message' => 'Une échéance n’est pas une date valide.'];
            $responsible = ($responsibles[$taskId] ?? '') === '' ? null : (int) $responsibles[$taskId];
            if ($responsible !== null && ($responsible < 1 || ! $this->db->table('employe')->where('id', $responsible)->countAllResults())) {
                return ['ok' => false, 'message' => 'Un responsable sélectionné n’existe pas.'];
            }

            $completed = null;
            if ($status === 'termine') {
                $completionInput = (string) ($completionDates[$taskId] ?? '');
                $completed = $completionInput === '' ? date('Y-m-d') : $this->optionalDate($completionInput);
                if ($completed === null) return ['ok' => false, 'message' => 'Une date de réalisation n’est pas valide.'];
            }
            $updates[] = ['id' => (int) $task['id'], 'statut' => $status, 'echeance' => $due, 'responsable_id' => $responsible, 'date_realisation' => $completed];
        }

        $done = count(array_filter($updates, static fn ($task) => $task['statut'] === 'termine'));
        $started = count(array_filter($updates, static fn ($task) => $task['statut'] !== 'a_faire'));
        $globalStatus = $done === 6 ? 'termine' : ($started > 0 ? 'en_cours' : 'a_faire');
        $this->db->transBegin();
        try {
            foreach ($updates as $update) {
                $taskId = $update['id'];
                unset($update['id']);
                if (! $this->db->table('onboarding_tache')->where('id', $taskId)->where('onboarding_id', $id)->update($update)) {
                    throw new \RuntimeException('Une tâche n’a pas pu être enregistrée.');
                }
            }
            $this->db->table('onboarding')->where('id', $id)->update([
                'statut' => $globalStatus,
                'termine_le' => $globalStatus === 'termine' ? date('Y-m-d H:i:s') : null,
            ]);
            $this->db->transCommit();
            return ['ok' => true, 'message' => 'Les six tâches ont été enregistrées.'];
        } catch (Throwable $e) {
            $this->db->transRollback();
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function optionalDate(string $value): ?string
    {
        if ($value === '') return null;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) return null;
        return $value;
    }
}
