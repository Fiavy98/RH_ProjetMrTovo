<?php

namespace App\Services\Bloc5;

use App\Models\Bloc5\AuditDossierModel;
use CodeIgniter\Database\BaseConnection;
use Throwable;

/**
 * Service métier du Bloc 5 — Dossier salarié et journal d'audit.
 */
class Bloc5Service
{
    private BaseConnection $db;
    private AuditDossierModel $auditModel;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->auditModel = new AuditDossierModel();
    }

    // =========================================================================
    // JOURNAL D'AUDIT & CONFIDENTIALITÉ (/bloc5/audit)
    // =========================================================================

    public function listeAudit(array $filters = []): array
    {
        try {
            $builder = $this->db->table('audit_dossier a')
                ->select('a.*, 
                    u.nom as auteur_nom, u.prenom as auteur_prenom, u.matricule as auteur_matricule, r.nom_role as auteur_role,
                    t.nom as cible_nom, t.prenom as cible_prenom, t.matricule as cible_matricule, d.nom_departement as cible_dept')
                ->join('employe u', 'u.id = a.utilisateur_id', 'left')
                ->join('role r', 'r.id = u.role_id', 'left')
                ->join('employe t', 't.id = a.employe_cible_id', 'left')
                ->join('departement d', 'd.id = t.departement_id', 'left')
                ->orderBy('a.date_action', 'DESC')
                ->limit(100);

            if (! empty($filters['categorie'])) {
                $builder->where('a.categorie_donnee', $filters['categorie']);
            }

            if (! empty($filters['statut'])) {
                $builder->where('a.statut_acces', $filters['statut']);
            }

            if (! empty($filters['recherche'])) {
                $search = $filters['recherche'];
                $builder->groupStart()
                    ->like('u.nom', $search)
                    ->orLike('u.prenom', $search)
                    ->orLike('t.nom', $search)
                    ->orLike('t.prenom', $search)
                    ->orLike('t.matricule', $search)
                    ->orLike('a.action', $search)
                    ->groupEnd();
            }

            $rows = $builder->get()->getResultArray();
            if (! empty($rows)) {
                return $rows;
            }
        } catch (Throwable $e) {
        }

        return $this->demoAudit();
    }

    public function statsAudit(): array
    {
        $rows = $this->listeAudit();
        $total = count($rows);
        $salaires = 0;
        $refuses = 0;

        foreach ($rows as $r) {
            if (($r['categorie_donnee'] ?? '') === 'REMUNERATION' || str_contains($r['action'] ?? '', 'SALAIRE')) {
                $salaires++;
            }
            if (($r['statut_acces'] ?? '') === 'REFUSE') {
                $refuses++;
            }
        }

        return [
            ['Événements récents', (string) $total],
            ['Accès rémunération', (string) $salaires],
            ['Accès non autorisés', (string) $refuses],
            ['Statut traçabilité', 'Actif (temps réel)'],
        ];
    }

    public function matriceHabilitations(): array
    {
        return [
            [
                'role' => 'Administrateur',
                'badge' => 'blue',
                'description' => 'Administration système et supervision complète.',
                'identite' => ['badge' => 'green', 'label' => 'Lecture & Écriture'],
                'coordonnees_cni' => ['badge' => 'green', 'label' => 'Lecture & Écriture'],
                'remuneration' => ['badge' => 'green', 'label' => 'Lecture & Écriture'],
                'documents' => ['badge' => 'green', 'label' => 'Accès complet'],
            ],
            [
                'role' => 'Ressources Humaines (RH)',
                'badge' => 'green',
                'description' => 'Gestion intégrale des dossiers administratifs et des carrières.',
                'identite' => ['badge' => 'green', 'label' => 'Lecture & Écriture'],
                'coordonnees_cni' => ['badge' => 'green', 'label' => 'Lecture & Écriture'],
                'remuneration' => ['badge' => 'green', 'label' => 'Lecture & Écriture'],
                'documents' => ['badge' => 'green', 'label' => 'Accès complet'],
            ],
            [
                'role' => 'Manager',
                'badge' => 'orange',
                'description' => 'Supervision opérationnelle de son équipe directe.',
                'identite' => ['badge' => 'blue', 'label' => 'Lecture (Département)'],
                'coordonnees_cni' => ['badge' => 'gray', 'label' => 'Téléphone pro uniquement'],
                'remuneration' => ['badge' => 'red', 'label' => 'Interdit'],
                'documents' => ['badge' => 'orange', 'label' => 'Fiche poste / Onboarding'],
            ],
            [
                'role' => 'Finance',
                'badge' => 'blue',
                'description' => 'Traitement de la comptabilité et préparation des paies.',
                'identite' => ['badge' => 'blue', 'label' => 'Lecture simple'],
                'coordonnees_cni' => ['badge' => 'gray', 'label' => 'Masqué'],
                'remuneration' => ['badge' => 'blue', 'label' => 'Lecture (Contrats & Salaires)'],
                'documents' => ['badge' => 'gray', 'label' => 'Pièces comptables'],
            ],
            [
                'role' => 'Direction',
                'badge' => 'green',
                'description' => 'Arbitrage stratégique, validations et rapports.',
                'identite' => ['badge' => 'blue', 'label' => 'Consultation globale'],
                'coordonnees_cni' => ['badge' => 'blue', 'label' => 'Consultation'],
                'remuneration' => ['badge' => 'blue', 'label' => 'Consultation'],
                'documents' => ['badge' => 'blue', 'label' => 'Consultation'],
            ],
        ];
    }

    public function simulerAcces(array $data, ?string $ip = null): array
    {
        $userId = (int) ($data['utilisateur_id'] ?? 0);
        $cibleId = (int) ($data['employe_cible_id'] ?? 0);
        $action = trim((string) ($data['action'] ?? 'CONSULTATION_DOSSIER'));
        $categorie = trim((string) ($data['categorie_donnee'] ?? 'IDENTITE'));
        $details = trim((string) ($data['details'] ?? ''));
        $statut = trim((string) ($data['statut_acces'] ?? 'AUTORISE'));

        if ($userId <= 0 || $cibleId <= 0) {
            return ['ok' => false, 'message' => 'L\'auteur et le salarié ciblé sont requis.'];
        }

        try {
            $this->auditModel->insert([
                'utilisateur_id'   => $userId,
                'employe_cible_id' => $cibleId,
                'action'           => $action,
                'categorie_donnee' => $categorie,
                'details'          => $details,
                'statut_acces'     => $statut,
                'ip_address'       => $ip ?? '127.0.0.1',
            ]);
            return ['ok' => true, 'message' => 'Événement d\'audit journalisé avec succès.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur d\'enregistrement : ' . $e->getMessage()];
        }
    }

    public function employesActifs(): array
    {
        try {
            return $this->db->table('employe e')
                ->select('e.id, e.matricule, e.nom, e.prenom, d.nom_departement')
                ->join('departement d', 'd.id = e.departement_id', 'left')
                ->where('e.actif', true)
                ->orderBy('e.nom', 'ASC')
                ->get()->getResultArray();
        } catch (Throwable $e) {
            return [
                ['id' => 1, 'matricule' => 'EMP-001', 'nom' => 'Manager', 'prenom' => 'Hery', 'nom_departement' => 'Informatique'],
                ['id' => 2, 'matricule' => 'EMP-002', 'nom' => 'RH', 'prenom' => 'Voahangy', 'nom_departement' => 'Ressources Humaines'],
                ['id' => 7, 'matricule' => 'EMP-007', 'nom' => 'Andrian', 'prenom' => 'Miora', 'nom_departement' => 'Finance'],
            ];
        }
    }

    private function demoAudit(): array
    {
        return [
            [
                'id' => 1, 'date_action' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
                'auteur_nom' => 'RH', 'auteur_prenom' => 'Voahangy', 'auteur_matricule' => 'EMP-002', 'auteur_role' => 'RH',
                'cible_nom' => 'Andrian', 'cible_prenom' => 'Miora', 'cible_matricule' => 'EMP-007', 'cible_dept' => 'Finance',
                'action' => 'CONSULTATION_SALAIRE', 'categorie_donnee' => 'REMUNERATION',
                'details' => 'Consultation de la fiche de rémunération et contrat', 'statut_acces' => 'AUTORISE', 'ip_address' => '127.0.0.1'
            ],
            [
                'id' => 2, 'date_action' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'auteur_nom' => 'Manager', 'auteur_prenom' => 'Hery', 'auteur_matricule' => 'EMP-001', 'auteur_role' => 'Manager',
                'cible_nom' => 'Rakoto', 'cible_prenom' => 'Nirina', 'cible_matricule' => 'EMP-008', 'cible_dept' => 'Commercial',
                'action' => 'CONSULTATION_SALAIRE', 'categorie_donnee' => 'REMUNERATION',
                'details' => 'Tentative de consultation du salaire hors département', 'statut_acces' => 'REFUSE', 'ip_address' => '192.168.1.45'
            ],
            [
                'id' => 3, 'date_action' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'auteur_nom' => 'RH', 'auteur_prenom' => 'Voahangy', 'auteur_matricule' => 'EMP-002', 'auteur_role' => 'RH',
                'cible_nom' => 'Manager', 'cible_prenom' => 'Hery', 'cible_matricule' => 'EMP-001', 'cible_dept' => 'Informatique',
                'action' => 'MODIFICATION_COORDONNEES', 'categorie_donnee' => 'IDENTITE',
                'details' => 'Mise à jour du numéro de téléphone et adresse', 'statut_acces' => 'AUTORISE', 'ip_address' => '127.0.0.1'
            ],
        ];
    }

    // =========================================================================
    // PAGES MAQUETTES STATIQUES (autres pages du Bloc 5)
    // =========================================================================

    public function screens(): array
    {
        return [
            'dossiers' => [
                'title' => 'Répertoire des salariés', 'subtitle' => 'Référentiel central des dossiers salariés',
                'description' => 'Recherche et accès aux dossiers administratifs.',
                'stats' => [['Salariés actifs', '128'], ['Dossiers à compléter', '07'], ['Documents à renouveler', '12']],
                'columns' => ['Matricule', 'Salarié', 'Département', 'Poste', 'Statut'],
                'rows' => [['EMP-001', 'Hery Manager', 'Informatique', 'Chef de projet', 'Actif'], ['EMP-002', 'Voahangy RH', 'RH', 'Responsable RH', 'Actif']],
            ],
            'dossier' => [
                'title' => 'Dossier administratif', 'subtitle' => 'Identité, coordonnées et situation professionnelle',
                'description' => 'Vue synthétique d’un dossier salarié, avec ses contrats, affectations, salaire et historique de carrière.',
                'stats' => [['Contrat', 'CDI'], ['Ancienneté', '3 ans'], ['Affectation', 'Administration']],
                'fields' => [['Identité', 'Nom, prénom, date de naissance, CNI, matricule'], ['Coordonnées', 'Adresse, téléphone, e-mail'], ['Situation', 'Département, poste, date d’embauche'], ['Rémunération', 'Salaire de référence · accès restreint']],
                'columns' => ['Événement de carrière', 'Poste / situation', 'Date d’effet'],
                'rows' => [['Embauche', 'Assistant administratif', '01/03/2023'], ['Changement de poste', 'Responsable RH', '01/06/2025']],
            ],
            'documents' => [
                'title' => 'Documents administratifs', 'subtitle' => 'Pièces du dossier et suivi de validité',
                'description' => 'Vue maquette pour classer les documents, repérer leur état et anticiper leur date d’expiration.',
                'stats' => [['Documents enregistrés', '342'], ['À renouveler', '12'], ['Expirés', '03']],
                'columns' => ['Document', 'Salarié', 'Date d’émission', 'Valide jusqu’au', 'État'],
                'rows' => [['Pièce d’identité CNI', 'Voahangy RH', '12/04/2024', '12/04/2029', 'Valide'], ['Certificat médical', 'Miora Andrian', '01/05/2025', '01/05/2026', 'À renouveler']],
            ],
            'carriere' => [
                'title' => 'Carrière et rémunération', 'subtitle' => 'Contrats, affectations, salaire et historique',
                'description' => 'Vue chronologique des changements professionnels et de rémunération.',
                'stats' => [['Contrats en cours', '116'], ['Affectations actives', '128'], ['Évolutions cette année', '09']],
                'columns' => ['Salarié', 'Événement', 'Poste / contrat', 'Salaire de référence', 'Date d’effet'],
                'rows' => [['Voahangy RH', 'Promotion', 'Responsable RH · CDI', 'Confidentiel', '01/06/2025'], ['Miora Andrian', 'Renouvellement', 'Comptable · CDD', 'Confidentiel', '01/01/2026']],
            ],
        ];
    }

    public function screen(string $slug): ?array
    {
        return $this->screens()[$slug] ?? null;
    }
}
