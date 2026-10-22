<?php

namespace App\Services\Bloc4;

use App\Entities\Contrat;
use App\Models\ContratModel;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use Throwable;

/** Règles métier des contrats du Bloc 4. */
class ContratService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function liste(array $filters = []): array
    {
        $builder = $this->db->table('contrat c')
            ->select('c.id, c.employe_id, c.type_contrat_id, c.contrat_precedent_id, c.salaire, c.date_debut, c.date_fin, c.date_signature, c.statut, e.matricule, e.nom, e.prenom, tc.code AS type_code, tc.libelle AS type_libelle')
            ->join('employe e', 'e.id = c.employe_id')
            ->join('type_contrat tc', 'tc.id = c.type_contrat_id')
            ->orderBy('c.date_debut', 'DESC');

        if (! empty($filters['type'])) {
            $builder->where('tc.code', $filters['type']);
        }
        if (! empty($filters['statut'])) {
            $builder->where('c.statut', $filters['statut']);
        }
        if (! empty($filters['employe'])) {
            $builder->groupStart()
                ->like('e.matricule', $filters['employe'])
                ->orLike('e.nom', $filters['employe'])
                ->orLike('e.prenom', $filters['employe'])
                ->groupEnd();
        }
        return $builder->get()->getResultArray();
    }

    public function options(): array
    {
        return [
            'employes' => $this->db->table('employe')->select('id, matricule, nom, prenom')->orderBy('nom')->get()->getResultArray(),
            'types' => $this->db->table('type_contrat')->select('id, code, libelle, duree_max_mois, nb_renouvellements_max')->orderBy('id')->get()->getResultArray(),
        ];
    }

    public function parametres(): array
    {
        return $this->db->table('type_contrat')->select('id, code, libelle, duree_max_mois, nb_renouvellements_max')->orderBy('id')->get()->getResultArray();
    }

    public function renouvelables(): array
    {
        return $this->db->table('contrat c')
            ->select('c.id, c.employe_id, c.date_debut, c.date_fin, e.matricule, e.nom, e.prenom, tc.code AS type_code, tc.nb_renouvellements_max')
            ->join('employe e', 'e.id = c.employe_id')
            ->join('type_contrat tc', 'tc.id = c.type_contrat_id')
            ->join('contrat enfant', 'enfant.contrat_precedent_id = c.id', 'left')
            ->where('c.statut', 'termine')
            ->where('c.date_fin IS NOT NULL', null, false)
            ->where('c.contrat_precedent_id IS NULL', null, false)
            ->where('tc.nb_renouvellements_max >', 0)
            ->where('enfant.id IS NULL', null, false)
            ->orderBy('c.date_fin', 'DESC')->get()->getResultArray();
    }

    public function detail(int $id): ?array
    {
        $contract = $this->db->table('contrat c')
            ->select('c.*, e.matricule, e.nom, e.prenom, tc.code AS type_code, tc.libelle AS type_libelle')
            ->join('employe e', 'e.id = c.employe_id')
            ->join('type_contrat tc', 'tc.id = c.type_contrat_id')
            ->where('c.id', $id)->get()->getRowArray();
        if ($contract === null) {
            return null;
        }

        $contract['renouvellements'] = $this->db->table('contrat c')
            ->select('c.id, c.date_debut, c.date_fin, c.statut, tc.code AS type_code')
            ->join('type_contrat tc', 'tc.id = c.type_contrat_id')
            ->where('c.contrat_precedent_id', $id)->get()->getResultArray();
        $contract['precedent'] = $contract['contrat_precedent_id']
            ? $this->db->table('contrat')->where('id', $contract['contrat_precedent_id'])->get()->getRowArray()
            : null;
        $contract['documents'] = $this->db->table('document')->where('contrat_id', $id)->orderBy('cree_le', 'DESC')->get()->getResultArray();
        return $contract;
    }

    public function enregistrer(array $input, ?int $id = null): array
    {
        $employeId = (int) ($input['employe_id'] ?? 0);
        $typeId = (int) ($input['type_contrat_id'] ?? 0);
        $start = $this->date($input['date_debut'] ?? '');
        $end = $this->date($input['date_fin'] ?? '');
        $salary = $input['salaire'] ?? '';
        $type = $this->db->table('type_contrat')->where('id', $typeId)->get()->getRowArray();

        if ($employeId < 1 || ! $this->db->table('employe')->where('id', $employeId)->countAllResults()) {
            return $this->fail('Sélectionnez un employé existant.');
        }
        if ($type === null) {
            return $this->fail('Sélectionnez un type de contrat valide.');
        }
        if (! in_array(strtoupper($type['code']), ['CE', 'CDD', 'CDI'], true)) {
            return $this->fail('Le Bloc 4 accepte uniquement les types CE, CDD et CDI.');
        }
        if ($start === null) {
            return $this->fail('La date de début est obligatoire et doit être valide.');
        }
        if (! is_numeric($salary) || (float) $salary < 0) {
            return $this->fail('Le salaire doit être un montant positif ou nul.');
        }

        $isCdi = strtoupper($type['code']) === 'CDI';
        if ($isCdi && $end !== null) {
            return $this->fail('Un CDI ne doit pas avoir de date de fin.');
        }
        if (! $isCdi && $end === null) {
            return $this->fail('La date de fin est obligatoire pour un CE ou un CDD.');
        }
        if (! $isCdi && $type['duree_max_mois'] === null) {
            return $this->fail('La durée maximale de ce type de contrat n’est pas paramétrée.');
        }
        if ($end !== null && $end < $start) {
            return $this->fail('La date de fin doit être postérieure ou égale à la date de début.');
        }
        if ($end !== null && $type['duree_max_mois'] !== null
            && ! $this->respecteDuree($start, $end, (int) $type['duree_max_mois'])) {
            return $this->fail('La durée dépasse la limite de ' . $type['duree_max_mois'] . ' mois calendaires pour ce type de contrat.');
        }

        if ($id !== null) {
            $existing = $this->db->table('contrat')->where('id', $id)->get()->getRowArray();
            if ($existing === null || $existing['statut'] !== Contrat::STATUT_BROUILLON) {
                return $this->fail('Seul un contrat brouillon existant peut être modifié.');
            }
            if ((int) $existing['employe_id'] !== $employeId) {
                return $this->fail('Le salarié ne peut pas être changé après la création du contrat.');
            }
            if ($existing['contrat_precedent_id'] !== null) {
                $previous = $this->db->table('contrat')->where('id', $existing['contrat_precedent_id'])->get()->getRowArray();
                $requiredStart = $previous && $previous['date_fin'] !== null
                    ? (new DateTimeImmutable($previous['date_fin']))->modify('+1 day')->format('Y-m-d')
                    : null;
                if ((int) $existing['type_contrat_id'] !== $typeId || $start !== $requiredStart) {
                    return $this->fail('Le type et le début d’un renouvellement sont liés au contrat précédent.');
                }
            }
        }

        if ($this->chevauchement($employeId, $start, $end, $id)) {
            return $this->fail('Ce contrat chevauche une autre période contractuelle de cet employé.');
        }

        $payload = [
            'employe_id' => $employeId,
            'type_contrat_id' => $typeId,
            'salaire' => $salary,
            'date_debut' => $start,
            'date_fin' => $end,
        ];
        if ($id === null) {
            $payload['statut'] = Contrat::STATUT_BROUILLON;
        }
        if (array_key_exists('date_signature', $input) && $input['date_signature'] !== '') {
            $signature = $this->date($input['date_signature']);
            if ($signature === null) {
                return $this->fail('La date de signature n’est pas valide.');
            }
            $payload['date_signature'] = $signature;
        }

        $model = model(ContratModel::class);
        try {
            $saved = $id === null ? $model->insert($payload) : $model->update($id, $payload);
        } catch (Throwable $e) {
            return $this->fail('Enregistrement refusé par la base : ' . $e->getMessage());
        }
        if ($saved === false) {
            return $this->fail(implode(' ', $model->errors()) ?: 'Le contrat n’a pas pu être enregistré.');
        }
        return ['ok' => true, 'id' => $id ?? (int) $model->getInsertID(), 'message' => 'Contrat enregistré en brouillon.'];
    }

    public function transition(int $id, string $action): array
    {
        $contract = $this->db->table('contrat')->where('id', $id)->get()->getRowArray();
        if ($contract === null) return $this->fail('Contrat introuvable.');
        $today = date('Y-m-d');

        $next = match ($action) {
            'signer' => $contract['statut'] === 'brouillon' ? 'signe' : null,
            'demarrer' => $contract['statut'] === 'signe' && $contract['date_debut'] <= $today ? 'en_cours' : null,
            'terminer' => $contract['statut'] === 'en_cours' && $contract['date_fin'] !== null && $contract['date_fin'] <= $today ? 'termine' : null,
            'annuler' => in_array($contract['statut'], ['brouillon', 'signe'], true) ? 'annule' : null,
            default => null,
        };
        if ($next === null) return $this->fail('Cette transition de statut n’est pas autorisée ou sa condition n’est pas remplie.');

        $payload = ['statut' => $next];
        if ($next === 'signe') $payload['date_signature'] = $today;

        $this->db->transBegin();
        try {
            if (! $this->db->table('contrat')->where('id', $id)->update($payload)) {
                throw new \RuntimeException('La mise à jour du contrat a échoué.');
            }
            if ($next === 'signe' && empty($contract['contrat_precedent_id'])) {
                $this->creerOnboardingSiAbsent((int) $contract['employe_id']);
            }
            $this->db->transCommit();
            return ['ok' => true, 'message' => 'Statut du contrat mis à jour : ' . $next . '.'];
        } catch (Throwable $e) {
            $this->db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    public function renouveler(int $id, array $input): array
    {
        $previous = $this->db->table('contrat')->where('id', $id)->get()->getRowArray();
        if ($previous === null) return $this->fail('Contrat précédent introuvable.');
        if ($previous['contrat_precedent_id'] !== null) return $this->fail('Un contrat de renouvellement ne peut pas être renouvelé.');
        if ($this->db->table('contrat')->where('contrat_precedent_id', $id)->countAllResults() > 0) {
            return $this->fail('Ce contrat a déjà été renouvelé.');
        }
        if ($previous['date_fin'] === null) return $this->fail('Un contrat sans date de fin ne peut pas être renouvelé.');
        if ($previous['statut'] !== 'termine') return $this->fail('Seul un contrat terminé peut être renouvelé.');

        $type = $this->db->table('type_contrat')->where('id', $previous['type_contrat_id'])->get()->getRowArray();
        if ($type === null || (int) $type['nb_renouvellements_max'] < 1) return $this->fail('Ce type de contrat ne permet pas de renouvellement.');
        $expectedStart = (new DateTimeImmutable($previous['date_fin']))->modify('+1 day')->format('Y-m-d');
        $providedStart = $input['date_debut'] ?? $expectedStart;
        if ($providedStart !== $expectedStart) return $this->fail('Le renouvellement doit commencer le lendemain de la fin du contrat précédent.');

        $input['employe_id'] = $previous['employe_id'];
        $input['type_contrat_id'] = $previous['type_contrat_id'];
        $input['date_debut'] = $expectedStart;
        $this->db->transBegin();
        try {
            $result = $this->enregistrer($input);
            if (! $result['ok']) {
                $this->db->transRollback();
                return $result;
            }
            if (! $this->db->table('contrat')->where('id', $result['id'])->update(['contrat_precedent_id' => $id])) {
                throw new \RuntimeException('Le lien au contrat précédent n’a pas pu être enregistré.');
            }
            $this->db->transCommit();
            return ['ok' => true, 'id' => $result['id'], 'message' => 'Renouvellement créé.'];
        } catch (Throwable $e) {
            $this->db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    public function dateDebutRenouvellement(int $id): ?string
    {
        $row = $this->db->table('contrat')->select('date_fin')->where('id', $id)->get()->getRowArray();
        return $row && $row['date_fin'] !== null
            ? (new DateTimeImmutable($row['date_fin']))->modify('+1 day')->format('Y-m-d')
            : null;
    }

    private function creerOnboardingSiAbsent(int $employeId): void
    {
        if ($this->db->table('onboarding')->where('employe_id', $employeId)->countAllResults() > 0) return;
        $types = $this->db->table('type_tache_onboarding')->orderBy('ordre')->get()->getResultArray();
        if (count($types) !== 6) throw new \RuntimeException('Les six types de tâches d’onboarding doivent être présents en base. Exécutez le script SQL de référence.');

        $this->db->table('onboarding')->insert(['employe_id' => $employeId, 'statut' => 'a_faire']);
        $onboardingId = (int) $this->db->insertID();
        foreach ($types as $type) {
            $this->db->table('onboarding_tache')->insert([
                'onboarding_id' => $onboardingId,
                'type_tache_id' => $type['id'],
                'statut' => 'a_faire',
            ]);
        }
    }

    private function chevauchement(int $employeId, string $start, ?string $end, ?int $excludeId): bool
    {
        $builder = $this->db->table('contrat')
            ->where('employe_id', $employeId)
            ->where('statut !=', 'annule')
            ->where('date_debut <=', $end ?? '9999-12-31')
            ->groupStart()->where('date_fin IS NULL', null, false)->orWhere('date_fin >=', $start)->groupEnd();
        if ($excludeId !== null) $builder->where('id !=', $excludeId);
        return $builder->countAllResults() > 0;
    }

    private function respecteDuree(string $start, string $end, int $months): bool
    {
        $startDate = new DateTimeImmutable($start);
        $exclusiveEnd = $this->ajouterMoisSansDepassement($startDate, $months);
        $dayAfterEnd = (new DateTimeImmutable($end))->modify('+1 day');
        return $dayAfterEnd <= $exclusiveEnd;
    }

    private function ajouterMoisSansDepassement(DateTimeImmutable $date, int $months): DateTimeImmutable
    {
        $monthIndex = ((int) $date->format('Y')) * 12 + ((int) $date->format('n') - 1) + $months;
        $year = intdiv($monthIndex, 12);
        $month = ($monthIndex % 12) + 1;
        $daysInTargetMonth = (int) $date->setDate($year, $month, 1)->format('t');
        $day = min((int) $date->format('j'), $daysInTargetMonth);
        return $date->setDate($year, $month, $day);
    }

    private function date(string $value): ?string
    {
        if ($value === '') return null;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) return null;
        return $date->format('Y-m-d');
    }

    private function fail(string $message): array
    {
        return ['ok' => false, 'message' => $message];
    }
}
