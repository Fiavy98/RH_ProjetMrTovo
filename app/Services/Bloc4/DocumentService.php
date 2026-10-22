<?php

namespace App\Services\Bloc4;

use App\Models\DocumentModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Database\BaseConnection;
use Throwable;

/** Stockage des pièces RH dans writable, avec métadonnées en base. */
class DocumentService
{
    private BaseConnection $db;
    private string $storagePath;

    public function __construct(?BaseConnection $db = null, ?string $storagePath = null)
    {
        $this->db = $db ?? db_connect();
        $this->storagePath = $storagePath ?? WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
    }

    public function liste(): array
    {
        return $this->db->table('document d')
            ->select('d.id, d.employe_id, d.contrat_id, d.type_document, d.nom_fichier, d.type_mime, d.taille_octets, d.cree_le, e.matricule, e.nom, e.prenom')
            ->join('employe e', 'e.id = d.employe_id')->orderBy('d.cree_le', 'DESC')
            ->get()->getResultArray();
    }

    public function options(): array
    {
        return [
            'employes' => $this->db->table('employe')->select('id, matricule, nom, prenom')->orderBy('nom')->get()->getResultArray(),
            'contrats' => $this->db->table('contrat c')->select('c.id, c.employe_id, tc.code, c.date_debut')
                ->join('type_contrat tc', 'tc.id = c.type_contrat_id')->orderBy('c.date_debut', 'DESC')->get()->getResultArray(),
        ];
    }

    public function enregistrer(array $input, ?UploadedFile $file): array
    {
        $employeeId = (int) ($input['employe_id'] ?? 0);
        $contractId = ($input['contrat_id'] ?? '') === '' ? null : (int) $input['contrat_id'];
        $type = (string) ($input['type_document'] ?? '');
        if (! in_array($type, ['contrat_signe', 'piece_identite', 'autre'], true)) return $this->fail('Sélectionnez un type de document valide.');
        if (! $this->db->table('employe')->where('id', $employeeId)->countAllResults()) return $this->fail('Sélectionnez un employé existant.');
        if ($contractId !== null && ! $this->db->table('contrat')->where('id', $contractId)->where('employe_id', $employeeId)->countAllResults()) {
            return $this->fail('Le contrat sélectionné ne correspond pas à cet employé.');
        }
        if ($file === null || ! $file->isValid() || $file->hasMoved()) return $this->fail('Choisissez un fichier valide à téléverser.');

        $mime = (string) $file->getMimeType();
        if (! in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'text/plain'], true)) {
            return $this->fail('Type de fichier non pris en charge. Formats admis : PDF, JPEG, PNG, WebP et texte.');
        }
        if (! is_dir($this->storagePath) && ! mkdir($this->storagePath, 0770, true) && ! is_dir($this->storagePath)) {
            return $this->fail('Le répertoire sécurisé de documents n’a pas pu être créé.');
        }

        $storedName = $file->getRandomName();
        try {
            $file->move($this->storagePath, $storedName);
            $relativePath = 'uploads/documents/' . $storedName;
            $model = model(DocumentModel::class);
            $id = $model->insert([
                'employe_id' => $employeeId,
                'contrat_id' => $contractId,
                'type_document' => $type,
                'nom_fichier' => $file->getClientName(),
                'chemin_fichier' => $relativePath,
                'type_mime' => $mime,
                'taille_octets' => $file->getSize(),
            ]);
            if ($id === false) throw new \RuntimeException(implode(' ', $model->errors()) ?: 'Les métadonnées du document n’ont pas pu être enregistrées.');
            return ['ok' => true, 'message' => 'Document ajouté au dossier.', 'id' => (int) $model->getInsertID()];
        } catch (Throwable $e) {
            $storedPath = $this->storagePath . DIRECTORY_SEPARATOR . $storedName;
            if (is_file($storedPath)) unlink($storedPath);
            return $this->fail($e->getMessage());
        }
    }

    public function cheminTelechargement(int $id): ?array
    {
        $doc = $this->db->table('document')->where('id', $id)->get()->getRowArray();
        if ($doc === null) return null;
        $base = realpath($this->storagePath);
        $path = realpath(WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $doc['chemin_fichier']));
        if ($base === false || $path === false || ! str_starts_with($path, $base . DIRECTORY_SEPARATOR) || ! is_file($path)) return null;
        return ['path' => $path, 'name' => $doc['nom_fichier'], 'mime' => $doc['type_mime'] ?: 'application/octet-stream'];
    }

    private function fail(string $message): array
    {
        return ['ok' => false, 'message' => $message];
    }
}
