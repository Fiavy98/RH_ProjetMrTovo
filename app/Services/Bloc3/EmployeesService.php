<?php

namespace App\Services\Bloc3;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class EmployeesService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function eligibleCandidates(): array
    {
        $usedCandidateIds = array_map(
            static fn (array $row): int => (int) ($row['candidat_id'] ?? 0),
            $this->db->table('employe')->select('candidat_id')->get()->getResultArray()
        );

        $rows = $this->db->table('proposition_embauche pe')
            ->select([
                'c.id AS candidate_id',
                'c.nom',
                'c.prenom',
                'c.email',
                'c.telephone',
                'pe.id AS proposition_id',
                'pe.reponse',
                'pe.decision_direction',
                'pe.date_proposition',
                'pe.salaire',
                'pe.date_debut',
                'offre_emploi.titre AS poste',
                'type_contrat.code AS type_contrat',
            ])
            ->join('candidature ca', 'ca.id = pe.candidature_id', 'left')
            ->join('candidat c', 'c.id = ca.candidat_id', 'left')
            ->join('offre_emploi', 'offre_emploi.id = ca.offre_emploi_id', 'left')
            ->join('type_contrat', 'type_contrat.id = pe.type_contrat_id', 'left')
            ->where('pe.reponse', 'ACCEPTEE')
            ->where('pe.decision_direction', 'VALIDEE')
            ->orderBy('c.nom', 'ASC')
            ->orderBy('c.prenom', 'ASC')
            ->get()
            ->getResultArray();

        $eligible = [];
        foreach ($rows as $row) {
            $candidateId = (int) ($row['candidate_id'] ?? 0);
            if ($candidateId <= 0 || in_array($candidateId, $usedCandidateIds, true)) {
                continue;
            }

            $eligible[] = [
                'candidate_id' => $candidateId,
                'nom' => (string) ($row['nom'] ?? ''),
                'prenom' => (string) ($row['prenom'] ?? ''),
                'email' => (string) ($row['email'] ?? ''),
                'telephone' => (string) ($row['telephone'] ?? ''),
                'poste' => (string) ($row['poste'] ?? '—'),
                'type_contrat' => (string) ($row['type_contrat'] ?? '—'),
                'salaire' => $row['salaire'] ?? 0,
                'date_debut' => $row['date_debut'] ?? null,
                'date_proposition' => $row['date_proposition'] ?? null,
                'decision_direction' => (string) ($row['decision_direction'] ?? ''),
            ];
        }

        return $eligible;
    }

    public function departmentOptions(): array
    {
        $rows = $this->db->table('departement')
            ->select(['id', 'nom_departement'])
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $options = ['' => 'Choisir un département'];
        foreach ($rows as $row) {
            $departmentId = (int) ($row['id'] ?? 0);
            if ($departmentId > 0) {
                $options[(string) $departmentId] = (string) ($row['nom_departement'] ?? 'Département');
            }
        }

        return $options;
    }

    public function candidateOptions(): array
    {
        $options = ['' => 'Choisir un candidat'];

        foreach ($this->eligibleCandidates() as $candidate) {
            $candidateId = (int) ($candidate['candidate_id'] ?? 0);
            if ($candidateId <= 0) {
                continue;
            }

            $label = trim((string) ($candidate['prenom'] ?? '') . ' ' . (string) ($candidate['nom'] ?? ''));
            $options[(string) $candidateId] = $label !== '' ? $label : 'Candidat #' . $candidateId;
        }

        return $options;
    }

    public function candidateEligibility(int $candidateId): ?array
    {
        $candidate = $this->db->table('candidat c')
            ->select([
                'c.id AS candidate_id',
                'c.nom',
                'c.prenom',
                'c.email',
                'ca.id AS candidature_id',
                'pe.id AS proposition_id',
                'pe.reponse',
                'pe.decision_direction',
                'offre_emploi.titre AS poste',
            ])
            ->join('candidature ca', 'ca.candidat_id = c.id', 'left')
            ->join('proposition_embauche pe', 'pe.candidature_id = ca.id', 'left')
            ->join('offre_emploi', 'offre_emploi.id = ca.offre_emploi_id', 'left')
            ->where('c.id', $candidateId)
            ->get()
            ->getRowArray();

        if ($candidate === null) {
            return null;
        }

        if ((string) ($candidate['reponse'] ?? '') !== 'ACCEPTEE') {
            return null;
        }

        $decisionDirection = (string) ($candidate['decision_direction'] ?? '');
        if ($decisionDirection !== 'VALIDEE') {
            return null;
        }

        $alreadyEmployee = $this->db->table('employe')->where('candidat_id', $candidateId)->get()->getRowArray();
        if (! empty($alreadyEmployee)) {
            return null;
        }

        return [
            'candidate_id' => (int) ($candidate['candidate_id'] ?? 0),
            'nom' => (string) ($candidate['nom'] ?? ''),
            'prenom' => (string) ($candidate['prenom'] ?? ''),
            'email' => (string) ($candidate['email'] ?? ''),
            'poste' => (string) ($candidate['poste'] ?? '—'),
            'proposition_id' => (int) ($candidate['proposition_id'] ?? 0),
        ];
    }

    public function createEmployeeFromCandidate(int $candidateId, array $data, ?UploadedFile $photoFile = null): array
    {
        $candidate = $this->candidateEligibility($candidateId);
        if ($candidate === null) {
            return ['ok' => false, 'message' => 'Ce candidat ne peut pas créer un dossier salarié.'];
        }

        $nom = trim((string) ($data['nom'] ?? $candidate['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? $candidate['prenom'] ?? ''));
        $email = trim((string) ($data['email'] ?? $candidate['email'] ?? ''));
        $dateEmbauche = trim((string) ($data['date_embauche'] ?? ''));
        $departementId = (int) ($data['departement_id'] ?? 0);
        $dateNaissance = trim((string) ($data['date_naissance'] ?? ''));
        $numeroCni = trim((string) ($data['numero_cni'] ?? ''));
        $dateDelivranceCni = trim((string) ($data['date_delivrance_cni'] ?? ''));
        $lieuDelivranceCni = trim((string) ($data['lieu_delivrance_cni'] ?? ''));
        $telephone = trim((string) ($data['telephone'] ?? ''));
        $adresse = trim((string) ($data['adresse'] ?? ''));
        $situationFamiliale = trim((string) ($data['situation_familiale'] ?? ''));

        if ($nom === '' || $prenom === '' || $email === '' || $dateEmbauche === '' || $departementId <= 0) {
            return ['ok' => false, 'message' => 'Les informations obligatoires du dossier salarié sont manquantes.'];
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'L’adresse e-mail du candidat est invalide.'];
        }

        if (! $this->isValidDate($dateEmbauche)) {
            return ['ok' => false, 'message' => 'La date d’embauche est invalide.'];
        }

        if ($dateNaissance !== '' && ! $this->isValidDate($dateNaissance)) {
            return ['ok' => false, 'message' => 'La date de naissance est invalide.'];
        }

        if ($dateDelivranceCni !== '' && ! $this->isValidDate($dateDelivranceCni)) {
            return ['ok' => false, 'message' => 'La date de délivrance de la CNI est invalide.'];
        }

        if ($numeroCni !== '' && $this->db->table('employe')->where('numero_cni', $numeroCni)->where('candidat_id !=', $candidateId)->countAllResults() > 0) {
            return ['ok' => false, 'message' => 'Ce numéro de CNI est déjà utilisé par un autre salarié.'];
        }

        $matricule = $this->generateMatricule();
        $photoPath = null;

        if ($photoFile !== null && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            $validation = $this->validatePhoto($photoFile);
            if ($validation['ok'] !== true) {
                return ['ok' => false, 'message' => $validation['message']];
            }

            $photoPath = $this->storePhoto($photoFile);
        }

        $insertData = [
            'matricule' => $matricule,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone !== '' ? $telephone : null,
            'adresse' => $adresse !== '' ? $adresse : null,
            'situation_familiale' => $situationFamiliale !== '' ? $situationFamiliale : null,
            'date_naissance' => $dateNaissance !== '' ? $dateNaissance : null,
            'numero_cni' => $numeroCni !== '' ? $numeroCni : null,
            'date_delivrance_cni' => $dateDelivranceCni !== '' ? $dateDelivranceCni : null,
            'lieu_delivrance_cni' => $lieuDelivranceCni !== '' ? $lieuDelivranceCni : null,
            'photo' => $photoPath,
            'departement_id' => $departementId,
            'role_id' => null,
            'candidat_id' => $candidateId,
            'date_embauche' => $dateEmbauche,
            'actif' => true,
        ];

        try {
            $this->db->transBegin();
            $inserted = $this->db->table('employe')->insert($insertData);

            if ($inserted === false || $this->db->affectedRows() <= 0) {
                throw new RuntimeException('L’insertion du dossier salarié a échoué.');
            }

            $this->db->transCommit();

            return ['ok' => true, 'message' => 'Le dossier salarié a bien été créé.'];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($photoPath !== null && is_file($this->storagePath($photoPath))) {
                unlink($this->storagePath($photoPath));
            }

            return ['ok' => false, 'message' => $e->getMessage() ?: 'Une erreur est survenue lors de la création du dossier salarié.'];
        }
    }

    private function generateMatricule(): string
    {
        $last = $this->db->table('employe')
            ->select('matricule')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $nextNumber = 1;
        if (! empty($last['matricule'])) {
            preg_match('/^(EMP-)(\d+)$/', (string) $last['matricule'], $matches);
            if (! empty($matches[2])) {
                $nextNumber = (int) $matches[2] + 1;
            }
        }

        return 'EMP-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function validatePhoto(UploadedFile $file): array
    {
        if ($file->getSize() <= 0) {
            return ['ok' => false, 'message' => 'Le fichier photo est vide.'];
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'La photo est trop volumineuse. La taille maximale est de 2 Mo.'];
        }

        $mime = $this->realMimeType($file->getTempName());
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($mime, $allowed, true)) {
            return ['ok' => false, 'message' => 'Type MIME de photo non autorisé. Utilisez JPG, PNG ou WEBP.'];
        }

        return ['ok' => true];
    }

    private function storePhoto(UploadedFile $file): string
    {
        $directory = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'employes';
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Le dossier de stockage des photos est inaccessible.');
        }

        $extension = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        $safeExtension = match ($extension) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };

        $storedName = bin2hex(random_bytes(12)) . '.' . $safeExtension;
        $fullPath = $directory . DIRECTORY_SEPARATOR . $storedName;

        if (! $file->move($directory, $storedName)) {
            throw new RuntimeException('La photo n’a pas pu être enregistrée.');
        }

        return 'uploads/employes/' . $storedName;
    }

    private function storagePath(string $storedPath): string
    {
        return WRITEPATH . str_replace(['uploads/', '/'], [ '', DIRECTORY_SEPARATOR ], $storedPath);
    }

    private function realMimeType(string $tempPath): string
    {
        if (! is_file($tempPath)) {
            return 'application/octet-stream';
        }

        $mime = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tempPath);
        if (is_string($mime) && $mime !== '') {
            return $mime;
        }

        return (string) mime_content_type($tempPath);
    }

    private function isValidDate(string $date): bool
    {
        $d = date_create_from_format('Y-m-d', $date);

        return $d instanceof \DateTimeInterface && $d->format('Y-m-d') === $date;
    }
}
