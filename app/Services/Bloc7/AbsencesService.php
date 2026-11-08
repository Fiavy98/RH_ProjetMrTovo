<?php

namespace App\Services\Bloc7;

use App\Models\Bloc7\AbsenceModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use DateTimeImmutable;
use InvalidArgumentException;

class AbsencesService
{
    private BaseConnection $db;
    private AbsenceModel $model;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->model = new AbsenceModel($this->db);
    }

    public function listing(array $filters): array
    {
        $builder = $this->db->table('absence a')->select('a.*, e.matricule, e.nom, e.prenom, d.nom_departement, tc.code AS type_code, tc.libelle AS type_libelle')
            ->join('employe e', 'e.id = a.employe_id')->join('departement d', 'd.id = e.departement_id', 'left')
            ->join('type_conge tc', 'tc.id = a.type_conge_id', 'left');
        if (! empty($filters['date_debut'])) $builder->where('a.date_fin >=', $filters['date_debut']);
        if (! empty($filters['date_fin'])) $builder->where('a.date_debut <=', $filters['date_fin']);
        if (! empty($filters['employe_id'])) $builder->where('a.employe_id', (int) $filters['employe_id']);
        if (! empty($filters['departement_id'])) $builder->where('e.departement_id', (int) $filters['departement_id']);
        if (! empty($filters['statut'])) $builder->where('a.statut', $filters['statut']);
        return $builder->orderBy('a.date_debut', 'DESC')->limit(250)->get()->getResultArray();
    }

    public function detail(int $id): ?array
    {
        $row = $this->db->table('absence a')->select('a.*, e.matricule, e.nom, e.prenom, d.nom_departement, tc.code AS type_code, tc.libelle AS type_libelle')
            ->join('employe e', 'e.id = a.employe_id')->join('departement d', 'd.id = e.departement_id', 'left')
            ->join('type_conge tc', 'tc.id = a.type_conge_id', 'left')->where('a.id', $id)->get()->getRowArray();
        if (! $row) return null;
        $row['historique'] = $this->db->table('absence_historique h')->select('h.*, e.nom, e.prenom')->join('employe e', 'e.id = h.auteur_id', 'left')
            ->where('h.absence_id', $id)->orderBy('h.cree_le', 'DESC')->get()->getResultArray();
        return $row;
    }

    public function save(array $data, ?UploadedFile $file, int $authorId, ?int $id = null): int
    {
        $employeeId = (int) ($data['employe_id'] ?? 0);
        $from = trim((string) ($data['date_debut'] ?? ''));
        $to = trim((string) ($data['date_fin'] ?? ''));
        $reason = trim((string) ($data['motif'] ?? ''));
        $status = (string) ($data['statut'] ?? 'ENREGISTREE');
        if (! $this->db->table('employe')->where('id', $employeeId)->countAllResults()) throw new InvalidArgumentException('Salarié introuvable.');
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $to);
        if (! $start || ! $end || $end < $start) throw new InvalidArgumentException('Les dates de l’absence sont invalides.');
        if ($reason === '') throw new InvalidArgumentException('Le motif est obligatoire.');
        if (! in_array($status, ['ENREGISTREE', 'A_VERIFIER', 'JUSTIFIEE', 'NON_JUSTIFIEE', 'ANNULEE'], true)) throw new InvalidArgumentException('Statut invalide.');
        $overlap = $this->db->table('absence')->where('employe_id', $employeeId)->where('statut !=', 'ANNULEE')
            ->where('date_debut <=', $to)->where('date_fin >=', $from);
        if ($id !== null) $overlap->where('id !=', $id);
        if ($overlap->countAllResults() > 0) throw new InvalidArgumentException('Cette absence chevauche une absence existante non annulée.');

        $typeId = (int) ($data['type_conge_id'] ?? 0);
        $type = $typeId > 0 ? $this->db->table('type_conge')->where('id', $typeId)->get()->getRowArray() : null;
        if ($typeId > 0 && ! $type) throw new InvalidArgumentException('Type de congé introuvable.');
        $current = $id ? $this->model->find($id) : null;
        if ($id && ! $current) throw new InvalidArgumentException('Absence introuvable.');
        $hasUpload = $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
        if (WorkBoolean::isTrue($type['justificatif_obligatoire'] ?? false) && ! $hasUpload && empty($current['justificatif_nom_stocke'])) {
            throw new InvalidArgumentException('Un justificatif est obligatoire pour ce type.');
        }

        $demandId = (int) ($data['demande_conge_id'] ?? 0);
        if ($demandId > 0) $this->assertApprovedDemand($demandId, $employeeId, $from, $to);
        $fileData = $this->storeFile($file);
        $storedPath = $fileData['_stored_path'] ?? null;
        unset($fileData['_stored_path']);
        $payload = [
            'employe_id' => $employeeId, 'type_conge_id' => $typeId ?: null, 'demande_conge_id' => $demandId ?: null,
            'date_debut' => $from, 'date_fin' => $to, 'motif' => $reason,
            'commentaire' => trim((string) ($data['commentaire'] ?? '')) ?: null,
            'justifiee' => in_array($status, ['JUSTIFIEE'], true), 'statut' => $status,
            'modifie_le' => date('Y-m-d H:i:s'), 'modifie_par' => $authorId,
        ] + $fileData;

        try {
            $this->db->transException(true)->transStart();
            if ($id) {
                $this->model->update($id, $payload);
                $savedId = $id;
                $action = 'MODIFICATION';
            } else {
                $payload['cree_par'] = $authorId;
                $savedId = (int) $this->model->insert($payload, true);
                $action = 'CREATION';
            }
            $this->db->table('absence_historique')->insert(['absence_id' => $savedId, 'action' => $action, 'detail' => 'Statut : ' . $status, 'auteur_id' => $authorId]);
            $this->db->transComplete();
            if (! $this->db->transStatus()) throw new \RuntimeException('L’absence n’a pas pu être enregistrée.');
        } catch (\Throwable $e) {
            if (is_string($storedPath) && is_file($storedPath)) {
                unlink($storedPath);
            }
            throw $e;
        }

        if ($id && is_string($storedPath) && ! empty($current['justificatif_nom_stocke'])) {
            $oldPath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'absences' . DIRECTORY_SEPARATOR . basename((string) $current['justificatif_nom_stocke']);
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }
        return $savedId;
    }

    public function options(): array
    {
        return [
            'employes' => $this->db->table('employe')->select('id, matricule, nom, prenom')->where('actif', true)->orderBy('nom')->get()->getResultArray(),
            'departements' => $this->db->table('departement')->select('id, nom_departement')->orderBy('nom_departement')->get()->getResultArray(),
            'types' => $this->db->table('type_conge')->select('id, code, libelle, justificatif_obligatoire')->where('actif', true)->orderBy('libelle')->get()->getResultArray(),
        ];
    }

    protected function storeFile(?UploadedFile $file): array
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) return [];
        if (! $file->isValid() || $file->hasMoved()) throw new InvalidArgumentException('Le justificatif reçu est invalide.');
        if ($file->getSize() > 5 * 1024 * 1024) throw new InvalidArgumentException('Le justificatif dépasse 5 Mo.');
        $allowed = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
        $mime = $file->getMimeType();
        if (! isset($allowed[$mime])) throw new InvalidArgumentException('Format autorisé : PDF, JPEG ou PNG.');
        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) throw new InvalidArgumentException('Extension de fichier non autorisée.');
        $stored = bin2hex(random_bytes(20)) . '.' . $allowed[$mime];
        $directory = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'absences';
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) throw new \RuntimeException('Stockage des justificatifs indisponible.');
        $size = $file->getSize();
        $original = basename($file->getClientName());
        $file->move($directory, $stored);
        return ['justificatif_nom_original' => $original, 'justificatif_nom_stocke' => $stored, 'justificatif_mime' => $mime, 'justificatif_taille' => $size, '_stored_path' => $directory . DIRECTORY_SEPARATOR . $stored];
    }

    private function assertApprovedDemand(int $id, int $employeeId, string $from, string $to): void
    {
        if (! $this->db->tableExists('demande_conge')) throw new InvalidArgumentException('Le module de demandes de congé n’est pas encore disponible.');
        $fields = $this->db->getFieldNames('demande_conge');
        foreach (['id', 'employe_id', 'date_debut', 'date_fin', 'statut'] as $field) {
            if (! in_array($field, $fields, true)) throw new InvalidArgumentException('Le schéma des demandes de congé n’est pas encore compatible.');
        }
        $row = $this->db->table('demande_conge')->where('id', $id)->get()->getRowArray();
        if (! $row || (int) $row['employe_id'] !== $employeeId || $row['date_debut'] > $from || $row['date_fin'] < $to || ! in_array(strtoupper((string) $row['statut']), ['APPROUVEE', 'APPROUVE', 'VALIDEE', 'VALIDE'], true)) {
            throw new InvalidArgumentException('La demande liée doit être approuvée, concerner le salarié et couvrir toute la période.');
        }
    }
}

final class WorkBoolean
{
    public static function isTrue(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 't', 'true'], true);
    }
}
