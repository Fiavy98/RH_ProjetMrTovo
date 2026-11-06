<?php

namespace App\Services\Bloc5;

use App\Models\DocumentModel;
use App\Models\EmployeModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

class DocumentsService
{
    private const FILE_SIZE_LIMIT = 5 * 1024 * 1024;
    private const TYPES = [
        'contrat_signe' => 'Contrat signé',
        'piece_identite' => 'Pièce d’identité',
        'autre' => 'Autre',
    ];

    private DocumentModel $documents;
    private EmployeModel $employees;
    private BaseConnection $db;
    private string $storageDir;

    public function __construct(
        ?DocumentModel $documents = null,
        ?EmployeModel $employees = null,
        ?BaseConnection $db = null,
        ?string $storageDir = null
    ) {
        $this->documents = $documents ?? model(DocumentModel::class);
        $this->employees = $employees ?? model(EmployeModel::class);
        $this->db = $db ?? db_connect();
        $this->storageDir = rtrim($storageDir ?? WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'documents', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function page(): array
    {
        return [
            'slug' => 'documents',
            'title' => 'Documents administratifs',
            'subtitle' => 'Pièces du dossier et suivi de validité',
            'description' => 'Consulter, ajouter, modifier et supprimer les documents administratifs du salarié.',
            'stats' => [
                ['Documents enregistrés', '0'],
                ['À surveiller', '0'],
                ['Expirés', '0'],
            ],
            'columns' => ['Document', 'Salarié', 'Date d’émission', 'Date d’expiration', 'État'],
            'rows' => [],
            'fields' => [
                ['Ajouter un document', 'Nom · type · fichier · dates d’émission et d’expiration'],
            ],
        ];
    }

    public function employee(int $employeeId): ?array
    {
        $employee = $this->db->table('employe e')
            ->select("e.id, e.matricule, e.nom, e.prenom, e.email, e.date_embauche, e.actif, d.nom_departement AS departement")
            ->join('departement d', 'd.id = e.departement_id', 'left')
            ->where('e.id', $employeeId)
            ->get()->getRowArray();

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

    public function documentTypes(): array
    {
        return self::TYPES;
    }

    public function contractsForEmployee(int $employeeId): array
    {
        return $this->db->table('contrat c')
            ->select('c.id, c.date_debut, c.date_fin, tc.code AS type_code, tc.libelle AS type_libelle')
            ->join('type_contrat tc', 'tc.id = c.type_contrat_id', 'left')
            ->where('c.employe_id', $employeeId)
            ->orderBy('c.date_debut', 'DESC')
            ->get()->getResultArray();
    }

    public function documentsForEmployee(int $employeeId, ?string $type = null): array
    {
        $builder = $this->db->table('document d')
            ->select('d.id, d.employe_id, d.contrat_id, d.type_document, d.nom_fichier, d.chemin_fichier, d.type_mime, d.taille_octets, d.date_emission, d.date_expiration, d.cree_le, c.date_debut AS contrat_date_debut, tc.code AS contrat_code')
            ->join('contrat c', 'c.id = d.contrat_id', 'left')
            ->join('type_contrat tc', 'tc.id = c.type_contrat_id', 'left')
            ->where('d.employe_id', $employeeId);

        if ($type !== null && $type !== '' && $type !== 'all') {
            $builder->where('d.type_document', $type);
        }

        $rows = $builder->orderBy('d.cree_le', 'DESC')->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['type_label'] = self::TYPES[$row['type_document']] ?? ucfirst((string) $row['type_document']);
            $row['etat'] = self::documentState($row['date_expiration'] ?? null);
            $row['date_emission'] = self::formatDate((string) ($row['date_emission'] ?? ''));
            $row['date_expiration'] = self::formatDate((string) ($row['date_expiration'] ?? ''));
            $row['cree_le'] = self::formatDatetime((string) ($row['cree_le'] ?? ''));
            $row['taille_octets'] = $this->formatBytes((int) ($row['taille_octets'] ?? 0));
        }
        unset($row);

        return $rows;
    }

    public function addDocument(int $employeeId, array $input, ?UploadedFile $file): array
    {
        $validation = $this->validateDocumentInput($employeeId, $input, $file, false);
        if ($validation['ok'] !== true) {
            return $validation;
        }

        $storedName = $this->storeUploadedFile($file);

        try {
            $insertId = $this->documents->insert([
                'employe_id' => $employeeId,
                'contrat_id' => $validation['contrat_id'],
                'type_document' => $validation['type_document'],
                'nom_fichier' => $file->getClientName(),
                'chemin_fichier' => 'uploads/documents/' . $storedName,
                'type_mime' => $validation['mime'],
                'taille_octets' => $file->getSize(),
                'date_emission' => $validation['date_emission'],
                'date_expiration' => $validation['date_expiration'],
                'cree_par' => (int) session()->get('auth_employee_id'),
            ]);

            if ($insertId === false || $this->documents->errors()) {
                $this->removeStoredFile($storedName);
                throw new RuntimeException(implode(' ', $this->documents->errors()) ?: 'Échec de l’enregistrement du document.');
            }

            return ['ok' => true, 'message' => 'Le document a bien été ajouté.'];
        } catch (\Throwable $e) {
            $this->removeStoredFile($storedName);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateDocument(int $employeeId, int $documentId, array $input, ?UploadedFile $file = null): array
    {
        $document = $this->documents->where('id', $documentId)->where('employe_id', $employeeId)->first();
        if ($document === null) {
            return ['ok' => false, 'message' => 'Ce document n’appartient pas au salarié sélectionné.'];
        }

        $validation = $this->validateDocumentInput($employeeId, $input, $file, true, $document);
        if ($validation['ok'] !== true) {
            return $validation;
        }

        $documentPath = $this->documentValue($document, 'chemin_fichier');
        $documentName = $this->documentValue($document, 'nom_fichier');
        $documentMime = $this->documentValue($document, 'type_mime');
        $documentSize = $this->documentValue($document, 'taille_octets');
        $newPath = $documentPath;
        $storedName = null;

        try {
            if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
                $storedName = $this->storeUploadedFile($file);
                $newPath = 'uploads/documents/' . $storedName;
            }

            $this->documents->update($documentId, [
                'contrat_id' => $validation['contrat_id'],
                'type_document' => $validation['type_document'],
                'nom_fichier' => $file !== null && $file->isValid() ? $file->getClientName() : $documentName,
                'chemin_fichier' => $newPath,
                'type_mime' => $file !== null && $file->isValid() ? $validation['mime'] : $documentMime,
                'taille_octets' => $file !== null && $file->isValid() ? $file->getSize() : $documentSize,
                'date_emission' => $validation['date_emission'],
                'date_expiration' => $validation['date_expiration'],
            ]);

            if ($this->documents->errors()) {
                throw new RuntimeException(implode(' ', $this->documents->errors()) ?: 'La mise à jour du document a échoué.');
            }

            if ($storedName !== null) {
                $this->removeStoredFile($documentPath, true);
            }

            return ['ok' => true, 'message' => 'Les informations du document ont bien été mises à jour.'];
        } catch (\Throwable $e) {
            if ($storedName !== null) {
                $this->removeStoredFile($storedName);
            }
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteDocument(int $employeeId, int $documentId): array
    {
        $document = $this->documents->where('id', $documentId)->where('employe_id', $employeeId)->first();
        if ($document === null) {
            return ['ok' => false, 'message' => 'Ce document n’appartient pas au salarié sélectionné.'];
        }

        $path = $this->resolveStoredPath((string) ($this->documentValue($document, 'chemin_fichier', '')));
        $this->documents->delete($documentId);

        if ($path !== null && is_file($path)) {
            unlink($path);
        }

        return ['ok' => true, 'message' => 'Le document a bien été supprimé.'];
    }

    public function downloadDocument(int $employeeId, int $documentId): ?array
    {
        $document = $this->documents->where('id', $documentId)->where('employe_id', $employeeId)->first();
        if ($document === null) {
            return null;
        }

        $path = $this->resolveStoredPath((string) ($this->documentValue($document, 'chemin_fichier', '')));
        if ($path === null || ! is_file($path)) {
            return null;
        }

        return [
            'path' => $path,
            'name' => (string) ($this->documentValue($document, 'nom_fichier', 'document')),
            'mime' => (string) ($this->documentValue($document, 'type_mime', 'application/octet-stream')),
        ];
    }

    public static function documentState(?string $dateExpiration): string
    {
        $value = trim((string) ($dateExpiration ?? ''));
        if ($value === '') {
            return 'Sans expiration';
        }

        $expiration = date_create_immutable($value);
        if ($expiration === false) {
            return 'Sans expiration';
        }

        $today = new \DateTimeImmutable('today');
        if ($expiration < $today) {
            return 'Expiré';
        }

        $warningDate = $today->modify('+30 days');
        if ($expiration <= $warningDate) {
            return 'Bientôt expiré';
        }

        return 'Valide';
    }

    private function documentValue($document, string $field, mixed $default = null): mixed
    {
        if (is_array($document)) {
            return $document[$field] ?? $default;
        }

        if (is_object($document)) {
            if (isset($document->{$field})) {
                return $document->{$field};
            }

            if (method_exists($document, 'toArray') && is_array($document->toArray())) {
                return $document->toArray()[$field] ?? $default;
            }
        }

        return $default;
    }

    private function validateDocumentInput(int $employeeId, array $input, ?UploadedFile $file, bool $allowEmptyFile, $existing = null): array
    {
        $typeDocument = trim((string) ($input['type_document'] ?? ''));
        if (! isset(self::TYPES[$typeDocument])) {
            return ['ok' => false, 'message' => 'Le type de document sélectionné est invalide.'];
        }

        $contractId = ($input['contrat_id'] ?? '') === '' ? null : (int) $input['contrat_id'];
        if ($contractId !== null && ! $this->db->table('contrat')->where('id', $contractId)->where('employe_id', $employeeId)->countAllResults()) {
            return ['ok' => false, 'message' => 'Le contrat sélectionné ne correspond pas au salarié.'];
        }

        $dateEmission = trim((string) ($input['date_emission'] ?? ''));
        $dateExpiration = trim((string) ($input['date_expiration'] ?? ''));
        if ($dateEmission !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEmission)) {
            return ['ok' => false, 'message' => 'La date d’émission est invalide.'];
        }
        if ($dateExpiration !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateExpiration)) {
            return ['ok' => false, 'message' => 'La date d’expiration est invalide.'];
        }

        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            if ($allowEmptyFile) {
                return ['ok' => true, 'type_document' => $typeDocument, 'contrat_id' => $contractId, 'date_emission' => $dateEmission, 'date_expiration' => $dateExpiration, 'mime' => $this->documentValue($existing, 'type_mime', 'application/octet-stream')];
            }
            return ['ok' => false, 'message' => 'Choisissez un fichier valide à téléverser.'];
        }

        if ($file->getSize() > self::FILE_SIZE_LIMIT) {
            return ['ok' => false, 'message' => 'Le fichier est trop volumineux. La taille maximale autorisée est de 5 Mo.'];
        }

        $mime = $this->realMimeType($file->getTempName());
        $allowedMime = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'text/plain'];
        if (! in_array($mime, $allowedMime, true)) {
            return ['ok' => false, 'message' => 'Type MIME non autorisé pour ce fichier.'];
        }

        return ['ok' => true, 'type_document' => $typeDocument, 'contrat_id' => $contractId, 'date_emission' => $dateEmission, 'date_expiration' => $dateExpiration, 'mime' => $mime];
    }

    private function storeUploadedFile(UploadedFile $file): string
    {
        if (! is_dir($this->storageDir) && ! mkdir($this->storageDir, 0775, true) && ! is_dir($this->storageDir)) {
            throw new RuntimeException('Le répertoire de stockage des documents est inaccessible.');
        }

        $extension = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        $extensions = ['pdf' => 'pdf', 'jpg' => 'jpg', 'jpeg' => 'jpg', 'png' => 'png', 'webp' => 'webp', 'txt' => 'txt'];
        $safeExt = $extensions[$extension] ?? 'bin';
        $storedName = bin2hex(random_bytes(16)) . '.' . $safeExt;

        if (! $file->move($this->storageDir, $storedName)) {
            throw new RuntimeException('Le fichier n’a pas pu être enregistré sur le serveur.');
        }

        return $storedName;
    }

    private function removeStoredFile(?string $pathOrName, bool $fromDb = false): void
    {
        if ($pathOrName === null || $pathOrName === '') {
            return;
        }

        $target = $fromDb ? $this->resolveStoredPath($pathOrName) : $this->storageDir . basename($pathOrName);
        if ($target !== null && is_file($target)) {
            unlink($target);
        }
    }

    private function resolveStoredPath(string $relativePath): ?string
    {
        $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relativePath);
        if ($normalized === '' || str_contains($normalized, '..')) {
            return null;
        }

        $base = realpath($this->storageDir);
        if ($base === false) {
            return null;
        }

        $fullPath = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($normalized, DIRECTORY_SEPARATOR);
        $fullPath = str_replace(['uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR, 'uploads/documents/'], '', $fullPath);
        $normalizedFull = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $fullPath);
        $resolved = realpath(dirname($normalizedFull)) ?: $base;
        if ($resolved === false) {
            return null;
        }

        $target = $resolved . DIRECTORY_SEPARATOR . basename($normalizedFull);
        if (! str_starts_with(realpath($target) ?: $target, $base)) {
            return null;
        }

        return $target;
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

        $mime = mime_content_type($tempPath);
        if (is_string($mime) && $mime !== '') {
            return $mime;
        }

        return 'application/octet-stream';
    }

    private static function formatDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '—';
        }

        $date = date_create_immutable($value);
        if ($date === false) {
            return $value;
        }

        return $date->format('d/m/Y');
    }

    private static function formatDatetime(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '—';
        }

        $date = date_create_immutable($value);
        if ($date === false) {
            return $value;
        }

        return $date->format('d/m/Y H:i');
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return round($value, 1) . ' ' . $units[$unitIndex];
    }
}
