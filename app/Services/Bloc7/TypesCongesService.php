<?php

namespace App\Services\Bloc7;

use App\Models\Bloc7\TypeCongeModel;
use CodeIgniter\Database\BaseConnection;
use InvalidArgumentException;

class TypesCongesService
{
    private BaseConnection $db;
    private TypeCongeModel $model;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->model = model(TypeCongeModel::class);
    }

    public function listing(string $search = ''): array
    {
        $builder = $this->db->table('type_conge tc')->select('tc.*, COUNT(DISTINCT a.id) AS absences_utilisees')->join('absence a', 'a.type_conge_id = tc.id', 'left')->groupBy('tc.id');
        if ($search !== '') $builder->groupStart()->like('tc.code', $search)->orLike('tc.libelle', $search)->groupEnd();
        return $builder->orderBy('tc.actif', 'DESC')->orderBy('tc.libelle')->get()->getResultArray();
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function save(array $data, int $authorId, ?int $id = null): int
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $label = trim((string) ($data['libelle'] ?? ''));
        $mode = trim((string) ($data['mode_decompte'] ?? '')) ?: null;
        if (! preg_match('/^[A-Z0-9_\-]{2,30}$/', $code)) throw new InvalidArgumentException('Le code doit contenir 2 à 30 lettres majuscules, chiffres, tirets ou underscores.');
        if ($label === '' || mb_strlen($label) > 120) throw new InvalidArgumentException('Le libellé est obligatoire (120 caractères maximum).');
        if ($mode !== null && ! in_array($mode, ['JOURS_OUVRES', 'JOURS_OUVRABLES', 'JOURS_CALENDAIRES', 'A_DEFINIR'], true)) throw new InvalidArgumentException('Mode de décompte invalide.');
        $duplicate = $this->db->table('type_conge')->where('code', $code);
        if ($id) $duplicate->where('id !=', $id);
        if ($duplicate->countAllResults()) throw new InvalidArgumentException('Ce code existe déjà.');
        $payload = [
            'code' => $code, 'libelle' => $label, 'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'actif' => ($data['actif'] ?? '0') === '1', 'justificatif_obligatoire' => ($data['justificatif_obligatoire'] ?? '0') === '1',
            'mode_decompte' => $mode, 'modifie_le' => date('Y-m-d H:i:s'), 'modifie_par' => $authorId,
        ];
        if ($id) {
            if (! $this->model->find($id)) throw new InvalidArgumentException('Type de congé introuvable.');
            $this->model->update($id, $payload);
            return $id;
        }
        $payload['cree_par'] = $authorId;
        return (int) $this->model->insert($payload, true);
    }

    public function disable(int $id, int $authorId): void
    {
        if (! $this->model->find($id)) throw new InvalidArgumentException('Type de congé introuvable.');
        $this->model->update($id, ['actif' => false, 'modifie_le' => date('Y-m-d H:i:s'), 'modifie_par' => $authorId]);
    }
}
