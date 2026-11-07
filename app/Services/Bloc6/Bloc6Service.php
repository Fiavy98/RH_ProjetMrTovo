<?php

namespace App\Services\Bloc6;

use App\Models\Bloc6\HoraireTravailModel;
use App\Models\Bloc6\PlanningTravailModel;
use App\Models\Bloc6\PointageModel;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use Throwable;

/**
 * Service métier du Bloc 6 — Temps et pointage.
 * Réservé exclusivement aux 3 fonctionnalités requises :
 * 1. Horaires et régimes de travail (/bloc6/horaires)
 * 2. Planning par salarié et période (/bloc6/planning)
 * 3. Entrées, sorties, retards et heures supplémentaires (/bloc6/pointages)
 */
class Bloc6Service
{
    private BaseConnection $db;
    private HoraireTravailModel $horaires;
    private PlanningTravailModel $plannings;
    private PointageModel $pointages;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->horaires = new HoraireTravailModel();
        $this->plannings = new PlanningTravailModel();
        $this->pointages = new PointageModel();
    }

    /**
     * Détermine si une valeur renvoyée par PostgreSQL est un booléen TRUE.
     * Compatible avec 't', 'true', true, 1, '1'.
     */
    private function isPgTrue($val): bool
    {
        if ($val === true || $val === 1 || $val === '1') {
            return true;
        }
        if (is_string($val)) {
            $v = strtolower(trim($val));
            return $v === 't' || $v === 'true';
        }
        return false;
    }

    // =========================================================================
    // 1. HORAIRES ET RÉGIMES DE TRAVAIL (/bloc6/horaires)
    // =========================================================================

    public function listeHoraires(array $filters = []): array
    {
        try {
            $builder = $this->db->table('horaire_travail')->orderBy('id', 'ASC');

            if (! empty($filters['recherche'])) {
                $builder->groupStart()
                    ->like('code', $filters['recherche'])
                    ->orLike('libelle', $filters['recherche'])
                    ->groupEnd();
            }

            if (isset($filters['actif']) && $filters['actif'] !== '') {
                $builder->where('actif', $filters['actif'] === '1' ? 't' : 'f');
            }

            $results = $builder->get()->getResultArray();
            if (! empty($results)) {
                return $results;
            }
        } catch (Throwable $e) {
        }

        return $this->demoHoraires();
    }

    public function statsHoraires(): array
    {
        $horaires = $this->listeHoraires();
        $actifs = count(array_filter($horaires, fn($h) => $this->isPgTrue($h['actif'] ?? false)));
        $totalHeures = 0;
        foreach ($horaires as $h) {
            $totalHeures += (float) ($h['heures_hebdo'] ?? 0);
        }
        $moyenneHebdo = count($horaires) > 0 ? round($totalHeures / count($horaires), 1) : 0;

        return [
            ['Régimes enregistrés', (string) count($horaires)],
            ['Régimes actifs', (string) $actifs],
            ['Moyenne hebdo', $moyenneHebdo . ' h'],
        ];
    }

    public function enregistrerHoraire(array $data, ?int $id = null): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $libelle = trim((string) ($data['libelle'] ?? ''));
        $debut = trim((string) ($data['heure_debut'] ?? '08:00:00'));
        $fin = trim((string) ($data['heure_fin'] ?? '17:00:00'));
        $pause = (int) ($data['pause_minutes'] ?? 60);
        $description = trim((string) ($data['description'] ?? ''));
        $actif = isset($data['actif']) && $data['actif'] === '1' ? 't' : 'f';

        if ($code === '' || $libelle === '' || $debut === '' || $fin === '') {
            return ['ok' => false, 'message' => 'Le code, le libellé, l\'heure de début et de fin sont obligatoires.'];
        }

        // Calcul automatique de la durée journalière et hebdomadaire
        $timeDebut = strtotime("2026-01-01 $debut");
        $timeFin = strtotime("2026-01-01 $fin");
        if ($timeFin < $timeDebut) {
            $timeFin += 86400; // Passage de minuit
        }
        $dureeMinutes = max(0, round(($timeFin - $timeDebut) / 60) - $pause);
        $heuresJour = round($dureeMinutes / 60, 2);
        $heuresHebdo = round($heuresJour * 5, 2);

        $payload = [
            'code'          => $code,
            'libelle'       => $libelle,
            'heure_debut'   => $debut,
            'heure_fin'     => $fin,
            'pause_minutes' => $pause,
            'heures_jour'   => $heuresJour,
            'heures_hebdo'  => $heuresHebdo,
            'description'   => $description,
            'actif'         => $actif,
        ];

        try {
            if ($id !== null && $id > 0) {
                $this->db->table('horaire_travail')->where('id', $id)->update($payload);
                return ['ok' => true, 'message' => 'Régime horaire modifié avec succès.', 'id' => $id];
            }

            $this->db->table('horaire_travail')->insert($payload);
            return ['ok' => true, 'message' => 'Nouveau régime horaire créé avec succès.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 2. PLANNING PAR SALARIÉ ET PÉRIODE (/bloc6/planning)
    // =========================================================================

    public function planningSemaine(string $dateRef, array $filters = []): array
    {
        $ref = DateTimeImmutable::createFromFormat('Y-m-d', $dateRef) ?: new DateTimeImmutable('today');
        $jourSemaine = (int) $ref->format('N'); // 1 = Lundi, 7 = Dimanche
        $lundi = $ref->modify('-' . ($jourSemaine - 1) . ' days');
        $dimanche = $lundi->modify('+6 days');

        $datesSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $cur = $lundi->modify("+{$i} days");
            $datesSemaine[] = [
                'date'        => $cur->format('Y-m-d'),
                'jour_nom'    => $this->nomJourFr((int) $cur->format('N')),
                'jour_num'    => $cur->format('d/m'),
                'est_weekend' => (int) $cur->format('N') >= 6,
            ];
        }

        $employes = $this->employesActifs($filters);

        $planningsParEmploye = [];
        try {
            $rows = $this->db->table('planning_travail pt')
                ->select('pt.*, ht.code as horaire_code, ht.libelle as horaire_libelle')
                ->join('horaire_travail ht', 'ht.id = pt.horaire_id', 'left')
                ->where('pt.date_travail >=', $lundi->format('Y-m-d'))
                ->where('pt.date_travail <=', $dimanche->format('Y-m-d'))
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $dateKey = substr((string) $r['date_travail'], 0, 10);
                $planningsParEmploye[$r['employe_id']][$dateKey] = $r;
            }
        } catch (Throwable $e) {
        }

        $grille = [];
        foreach ($employes as $emp) {
            $empId = $emp['id'];
            $jours = [];
            $totalSemaine = 0.0;

            foreach ($datesSemaine as $d) {
                $dateStr = $d['date'];
                $item = $planningsParEmploye[$empId][$dateStr] ?? null;

                if ($item !== null) {
                    $estRepos = $this->isPgTrue($item['est_jour_repos'] ?? false);
                    $heures = $estRepos ? 0.0 : (float) ($item['heures_prevues'] ?? 8.0);
                    $debut = substr((string) ($item['heure_debut_prevue'] ?? '08:00'), 0, 5);
                    $fin = substr((string) ($item['heure_fin_prevue'] ?? '17:00'), 0, 5);
                    $planifie = true;
                } else {
                    $estRepos = $d['est_weekend'];
                    $heures = 0.0;
                    $debut = null;
                    $fin = null;
                    $planifie = false;
                }

                if (! $estRepos && $planifie) {
                    $totalSemaine += $heures;
                }

                $jours[$dateStr] = [
                    'date'        => $dateStr,
                    'est_repos'   => $estRepos,
                    'planifie'    => $planifie,
                    'heure_debut' => $debut,
                    'heure_fin'   => $fin,
                    'heures'      => $heures,
                ];
            }

            $grille[] = [
                'employe'      => $emp,
                'jours'        => $jours,
                'total_heures' => round($totalSemaine, 2),
            ];
        }

        return [
            'dateRef'      => $dateRef,
            'lundi'        => $lundi->format('Y-m-d'),
            'dimanche'     => $dimanche->format('Y-m-d'),
            'datesSemaine' => $datesSemaine,
            'grille'       => $grille,
            'prevWeek'     => $lundi->modify('-7 days')->format('Y-m-d'),
            'nextWeek'     => $lundi->modify('+7 days')->format('Y-m-d'),
        ];
    }

    public function affecterPlanning(array $data, ?int $creePar = null): array
    {
        $employeId = (int) ($data['employe_id'] ?? 0);
        $horaireId = (int) ($data['horaire_id'] ?? 0);
        $dateDebut = trim((string) ($data['date_debut'] ?? ''));
        $dateFin = trim((string) ($data['date_fin'] ?? ''));
        $exclureWeekend = ! empty($data['exclure_weekend']);

        if ($employeId <= 0 || $horaireId <= 0 || $dateDebut === '' || $dateFin === '') {
            return ['ok' => false, 'message' => 'Veuillez sélectionner un salarié, un régime horaire et la période de dates.'];
        }

        try {
            $horaire = $this->db->table('horaire_travail')->where('id', $horaireId)->get()->getRowArray();
            if (! $horaire) {
                // Recherche dans les démos si table non créée
                foreach ($this->demoHoraires() as $dh) {
                    if ($dh['id'] == $horaireId) {
                        $horaire = $dh;
                        break;
                    }
                }
            }

            if (! $horaire) {
                return ['ok' => false, 'message' => 'Régime horaire introuvable.'];
            }

            $cur = new DateTimeImmutable($dateDebut);
            $fin = new DateTimeImmutable($dateFin);
            $count = 0;

            while ($cur <= $fin) {
                $dateStr = $cur->format('Y-m-d');
                $isWeekend = (int) $cur->format('N') >= 6;
                $estRepos = $exclureWeekend && $isWeekend;

                $row = [
                    'employe_id'           => $employeId,
                    'date_travail'         => $dateStr,
                    'horaire_id'           => $horaire['id'],
                    'heure_debut_prevue'   => $horaire['heure_debut'],
                    'heure_fin_prevue'     => $horaire['heure_fin'],
                    'pause_minutes_prevue' => $horaire['pause_minutes'],
                    'heures_prevues'       => $estRepos ? 0.00 : (float) $horaire['heures_jour'],
                    'est_jour_repos'       => $estRepos ? 't' : 'f',
                    'cree_par'             => $creePar,
                ];

                $existing = $this->db->table('planning_travail')
                    ->where('employe_id', $employeId)
                    ->where('date_travail', $dateStr)
                    ->get()->getRowArray();

                if ($existing) {
                    $this->db->table('planning_travail')
                        ->where('id', $existing['id'])
                        ->update($row);
                } else {
                    $this->db->table('planning_travail')->insert($row);
                }

                $count++;
                $cur = $cur->modify('+1 day');
            }

            return ['ok' => true, 'message' => "$count jour(s) planifié(s) avec succès."];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur lors de la planification : ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 3. POINTAGES, RETARDS ET HEURES SUPPLÉMENTAIRES (/bloc6/pointages)
    // =========================================================================

    public function listePointages(array $filters = []): array
    {
        $date = ! empty($filters['date']) ? $filters['date'] : date('Y-m-d');

        try {
            $builder = $this->db->table('pointage p')
                ->select('p.*, e.matricule, e.nom, e.prenom, d.nom_departement, pt.heure_debut_prevue, pt.heure_fin_prevue, pt.heures_prevues')
                ->join('employe e', 'e.id = p.employe_id')
                ->join('departement d', 'd.id = e.departement_id', 'left')
                ->join('planning_travail pt', 'pt.employe_id = p.employe_id AND pt.date_travail = p.date_pointage', 'left')
                ->where('p.date_pointage', $date)
                ->orderBy('p.heure_entree', 'ASC');

            if (! empty($filters['departement'])) {
                $builder->where('e.departement_id', $filters['departement']);
            }

            if (! empty($filters['employe'])) {
                $builder->groupStart()
                    ->like('e.matricule', $filters['employe'])
                    ->orLike('e.nom', $filters['employe'])
                    ->orLike('e.prenom', $filters['employe'])
                    ->groupEnd();
            }

            if (! empty($filters['statut_pointage'])) {
                if ($filters['statut_pointage'] === 'retard') {
                    $builder->where('p.retard_minutes >', 0);
                } elseif ($filters['statut_pointage'] === 'heures_sup') {
                    $builder->where('p.heures_supplementaires >', 0);
                } elseif ($filters['statut_pointage'] === 'incomplet') {
                    $builder->where('p.heure_sortie IS NULL');
                }
            }

            $rows = $builder->get()->getResultArray();

            // Recalcul précis des heures supplémentaires par rapport à l'heure de fin prévue
            foreach ($rows as &$r) {
                if (! empty($r['heure_sortie'])) {
                    $finPrevue = ! empty($r['heure_fin_prevue']) ? $r['heure_fin_prevue'] : '17:00:00';
                    $debutPrevu = ! empty($r['heure_debut_prevue']) ? $r['heure_debut_prevue'] : '08:00:00';

                    $timeSortie = strtotime($r['date_pointage'] . ' ' . $r['heure_sortie']);
                    $timeFin = strtotime($r['date_pointage'] . ' ' . $finPrevue);
                    $timeDeb = strtotime($r['date_pointage'] . ' ' . $debutPrevu);

                    if ($timeFin < $timeDeb) {
                        $timeFin += 86400; // Passage de minuit
                    }

                    if ($timeSortie > $timeFin) {
                        $minSup = max(0, (int) round(($timeSortie - $timeFin) / 60));
                        $r['heures_supplementaires'] = round($minSup / 60, 2);
                    } else {
                        $r['heures_supplementaires'] = 0.00;
                    }
                }
            }
            unset($r);

            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function statsPointages(string $date): array
    {
        $rows = $this->listePointages(['date' => $date]);
        $total = count($rows);
        $retards = 0;
        $totalHeuresSup = 0.0;
        $incomplets = 0;

        foreach ($rows as $r) {
            if ((int) ($r['retard_minutes'] ?? 0) > 0) {
                $retards++;
            }
            $totalHeuresSup += (float) ($r['heures_supplementaires'] ?? 0);
            if (empty($r['heure_sortie'])) {
                $incomplets++;
            }
        }

        return [
            ['Pointages du jour', (string) $total],
            ['Retards détectés', (string) $retards],
            ['Heures supplémentaires', round($totalHeuresSup, 1) . ' h'],
            ['Pointages incomplets', (string) $incomplets],
        ];
    }

    public function enregistrerPointage(array $data, ?int $modifiePar = null): array
    {
        $employeId = (int) ($data['employe_id'] ?? 0);
        $datePointage = trim((string) ($data['date_pointage'] ?? date('Y-m-d')));
        $heureEntree = trim((string) ($data['heure_entree'] ?? ''));
        $heureSortie = trim((string) ($data['heure_sortie'] ?? ''));
        $pauseMinutes = (int) ($data['pause_minutes'] ?? 60);
        $motif = trim((string) ($data['motif_correction'] ?? ''));

        if ($employeId <= 0 || $datePointage === '' || $heureEntree === '') {
            return ['ok' => false, 'message' => 'Le salarié, la date et l\'heure d\'entrée sont obligatoires.'];
        }

        // Vérification obligatoire : le salarié doit avoir un planning défini pour cette date
        $planning = null;
        try {
            $planning = $this->db->table('planning_travail')
                ->where('employe_id', $employeId)
                ->where('date_travail', $datePointage)
                ->get()->getRowArray();
        } catch (Throwable $e) {
        }

        $dateFr = date('d/m/Y', strtotime($datePointage));
        if (! $planning) {
            return [
                'ok' => false,
                'message' => "Impossible d'enregistrer le pointage : ce salarié n'a pas de planning pour le $dateFr. Veuillez d'abord lui affecter un planning."
            ];
        }

        if ($this->isPgTrue($planning['est_jour_repos'] ?? false)) {
            return [
                'ok' => false,
                'message' => "Impossible d'enregistrer le pointage : ce salarié est en jour de repos pour le $dateFr."
            ];
        }

        $debutPrevu = ! empty($planning['heure_debut_prevue']) ? $planning['heure_debut_prevue'] : '08:00:00';
        $finPrevue = ! empty($planning['heure_fin_prevue']) ? $planning['heure_fin_prevue'] : '17:00:00';
        $heuresPrevues = $planning['heures_prevues'] !== null ? (float) $planning['heures_prevues'] : 8.00;

        $dateFr = date('d/m/Y', strtotime($datePointage));
        if (! $planning) {
            return [
                'ok' => false,
                'message' => "Impossible d'enregistrer le pointage : ce salarié n'a pas de planning pour le $dateFr. Veuillez d'abord lui affecter un planning."
            ];
        }

        if ($this->isPgTrue($planning['est_jour_repos'] ?? false)) {
            return [
                'ok' => false,
                'message' => "Impossible d'enregistrer le pointage : ce salarié est en jour de repos pour le $dateFr."
            ];
        }

        $debutPrevu = ! empty($planning['heure_debut_prevue']) ? $planning['heure_debut_prevue'] : '08:00:00';
        $finPrevue = ! empty($planning['heure_fin_prevue']) ? $planning['heure_fin_prevue'] : '17:00:00';
        $heuresPrevues = $planning['heures_prevues'] !== null ? (float) $planning['heures_prevues'] : 8.00;

        // Calcul du retard
        $timeEntree = strtotime("$datePointage $heureEntree");
        $timePrevu = strtotime("$datePointage $debutPrevu");
        $retardMinutes = max(0, (int) round(($timeEntree - $timePrevu) / 60));

        // Calcul du temps travaillé et des heures sup si sortie renseignée
        $tempsTravailleHeures = null;
        $heuresSup = 0.00;
        if ($heureSortie !== '') {
            $timeSortie = strtotime("$datePointage $heureSortie");
            if ($timeSortie < $timeEntree) {
                $timeSortie += 86400;
            }
            $dureeNetteMinutes = max(0, round(($timeSortie - $timeEntree) / 60) - $pauseMinutes);
            $tempsTravailleHeures = round($dureeNetteMinutes / 60, 2);

            // Heures supplémentaires : temps presté après l'heure de fin prévue
            $timeFinPrevue = strtotime("$datePointage $finPrevue");
            if ($timeFinPrevue < $timePrevu) {
                $timeFinPrevue += 86400; // Passage de minuit
            }

            if ($timeSortie > $timeFinPrevue) {
                $minutesSup = max(0, (int) round(($timeSortie - $timeFinPrevue) / 60));
                $heuresSup = round($minutesSup / 60, 2);
            } else {
                $heuresSup = 0.00;
            }
        }

        $payload = [
            'employe_id'             => $employeId,
            'date_pointage'          => $datePointage,
            'heure_entree'           => $heureEntree,
            'heure_sortie'           => $heureSortie !== '' ? $heureSortie : null,
            'pause_minutes'          => $pauseMinutes,
            'temps_travaille_heures' => $tempsTravailleHeures,
            'retard_minutes'         => $retardMinutes,
            'heures_supplementaires' => $heuresSup,
            'statut'                 => $motif !== '' ? 'CORRIGE' : 'VALIDE',
            'motif_correction'       => $motif !== '' ? $motif : null,
            'modifie_par'            => $modifiePar,
        ];

        try {
            $existing = $this->db->table('pointage')
                ->where('employe_id', $employeId)
                ->where('date_pointage', $datePointage)
                ->get()->getRowArray();

            if ($existing) {
                $this->db->table('pointage')->where('id', $existing['id'])->update($payload);
                return ['ok' => true, 'message' => 'Pointage mis à jour avec succès.', 'id' => $existing['id']];
            }

            $newId = $this->db->table('pointage')->insert($payload);
            return ['ok' => true, 'message' => 'Pointage enregistré avec succès.', 'id' => $newId];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // OPTIONS & RÉFÉRENTIELS
    // =========================================================================

    /**
     * Récupère uniquement les salariés ayant un planning de travail actif (non-repos)
     * pour une date donnée.
     */
    public function employesPlanifies(string $date): array
    {
        try {
            $rows = $this->db->table('planning_travail pt')
                ->select('e.id, e.matricule, e.nom, e.prenom, d.nom_departement, pt.heure_debut_prevue, pt.heure_fin_prevue, pt.est_jour_repos')
                ->join('employe e', 'e.id = pt.employe_id')
                ->join('departement d', 'd.id = e.departement_id', 'left')
                ->where('pt.date_travail', $date)
                ->where('e.actif', true)
                ->orderBy('e.nom', 'ASC')
                ->get()->getResultArray();

            $planifies = [];
            foreach ($rows as $r) {
                if (! $this->isPgTrue($r['est_jour_repos'] ?? false)) {
                    $planifies[] = $r;
                }
            }
            return $planifies;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Retourne tous les plannings actifs groupés par date (YYYY-MM-DD),
     * pour alimenter dynamiquement le sélecteur d'employés dans le formulaire de pointage.
     */
    public function planningsActifs(): array
    {
        try {
            $rows = $this->db->table('planning_travail pt')
                ->select('pt.employe_id, pt.date_travail, pt.heure_debut_prevue, pt.heure_fin_prevue, pt.est_jour_repos, e.matricule, e.nom, e.prenom')
                ->join('employe e', 'e.id = pt.employe_id')
                ->where('e.actif', true)
                ->orderBy('e.nom', 'ASC')
                ->get()->getResultArray();

            $parDate = [];
            foreach ($rows as $r) {
                if (! $this->isPgTrue($r['est_jour_repos'] ?? false)) {
                    $d = substr((string) $r['date_travail'], 0, 10);
                    $parDate[$d][] = [
                        'id'        => (int) $r['employe_id'],
                        'matricule' => $r['matricule'],
                        'nom'       => $r['nom'],
                        'prenom'    => $r['prenom'],
                        'debut'     => substr((string) ($r['heure_debut_prevue'] ?? '08:00'), 0, 5),
                        'fin'       => substr((string) ($r['heure_fin_prevue'] ?? '17:00'), 0, 5),
                    ];
                }
            }
            return $parDate;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function options(): array
    {
        return [
            'employes'     => $this->employesActifs(),
            'departements' => $this->listeDepartements(),
            'horaires'     => $this->listeHoraires(['actif' => '1']),
        ];
    }

    public function employesActifs(array $filters = []): array
    {
        try {
            $builder = $this->db->table('employe e')
                ->select('e.id, e.matricule, e.nom, e.prenom, d.nom_departement')
                ->join('departement d', 'd.id = e.departement_id', 'left')
                ->where('e.actif', true)
                ->orderBy('e.nom', 'ASC');

            if (! empty($filters['departement'])) {
                $builder->where('e.departement_id', $filters['departement']);
            }
            if (! empty($filters['employe'])) {
                $builder->groupStart()
                    ->like('e.matricule', $filters['employe'])
                    ->orLike('e.nom', $filters['employe'])
                    ->orLike('e.prenom', $filters['employe'])
                    ->groupEnd();
            }

            $res = $builder->get()->getResultArray();
            if (! empty($res)) {
                return $res;
            }
        } catch (Throwable $e) {
        }

        return [];
    }

    private function listeDepartements(): array
    {
        try {
            return $this->db->table('departement')->select('id, nom_departement')->orderBy('nom_departement')->get()->getResultArray();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function nomJourFr(int $dow): string
    {
        return match ($dow) {
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
            default => '',
        };
    }

    private function demoHoraires(): array
    {
        return [
            ['id' => 1, 'code' => 'STANDARD_40H', 'libelle' => 'Temps plein standard (08h00 - 17h00)', 'heure_debut' => '08:00:00', 'heure_fin' => '17:00:00', 'pause_minutes' => 60, 'heures_jour' => 8.00, 'heures_hebdo' => 40.00, 'actif' => true, 'description' => 'Régime 5 jours standard'],
            ['id' => 2, 'code' => 'EQUIPE_MATIN', 'libelle' => 'Équipe du matin (06h00 - 14h00)', 'heure_debut' => '06:00:00', 'heure_fin' => '14:00:00', 'pause_minutes' => 30, 'heures_jour' => 7.50, 'heures_hebdo' => 37.50, 'actif' => true, 'description' => 'Shift matin avec 30 min de pause'],
            ['id' => 3, 'code' => 'EQUIPE_SOIR', 'libelle' => 'Équipe du soir (14h00 - 22h00)', 'heure_debut' => '14:00:00', 'heure_fin' => '22:00:00', 'pause_minutes' => 30, 'heures_jour' => 7.50, 'heures_hebdo' => 37.50, 'actif' => true, 'description' => 'Shift après-midi / soir'],
            ['id' => 4, 'code' => 'MI_TEMPS', 'libelle' => 'Temps partiel matin (08h00 - 12h00)', 'heure_debut' => '08:00:00', 'heure_fin' => '12:00:00', 'pause_minutes' => 0, 'heures_jour' => 4.00, 'heures_hebdo' => 20.00, 'actif' => true, 'description' => 'Matinées 20h sans pause'],
        ];
    }
}
