<?php

namespace App\Models;

use App\Entities\Employe;

/**
 * Accès à la table `employe`.
 * Origine : Bloc 1 (table partagée par tous les blocs).
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class EmployeModel extends SirhModel
{
    protected $table      = 'employe';
    protected $returnType = Employe::class;

    // `mot_de_passe` reste absent : ce module utilise une sélection sans mot de passe.
    protected $allowedFields = [
        'matricule',
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'situation_familiale',
        'date_naissance',
        'numero_cni',
        'date_delivrance_cni',
        'lieu_delivrance_cni',
        'photo',
        'departement_id',
        'role_id',
        'candidat_id',
        'date_embauche',
        'actif',
        'date_naissance',
        'telephone',
        'adresse',
        'situation_familiale',
        'numero_cni',
        'date_delivrance_cni',
        'lieu_delivrance_cni',
        'photo',
    ];

    protected $validationRules = [
        'matricule'             => 'required|max_length[20]',
        'nom'                   => 'required|max_length[100]',
        'prenom'                => 'required|max_length[100]',
        'email'                 => 'required|valid_email|max_length[255]',
        'telephone'             => 'permit_empty|max_length[30]',
        'adresse'               => 'permit_empty',
        'situation_familiale'   => 'permit_empty|max_length[50]',
        'date_naissance'        => 'permit_empty|valid_date[Y-m-d]',
        'numero_cni'            => 'permit_empty|max_length[30]',
        'date_delivrance_cni'   => 'permit_empty|valid_date[Y-m-d]',
        'lieu_delivrance_cni'  => 'permit_empty|max_length[150]',
        'photo'                 => 'permit_empty|max_length[255]',
        'departement_id'        => 'required|is_natural_no_zero',
        'role_id'               => 'permit_empty|is_natural_no_zero',
        'candidat_id'           => 'required|is_natural_no_zero',
        'date_embauche'         => 'required|valid_date[Y-m-d]',
        'actif'                 => 'permit_empty',
    ];

    public function filtered(array $filters = [], int $offset = 0, int $limit = 10): array
    {
        $builder = $this->db->table('employe e')
            ->select("e.id, e.matricule, e.nom, e.prenom, e.photo, e.date_embauche, e.actif, d.nom_departement AS departement, COALESCE(p.intitule, '—') AS poste_actuel")
            ->join('departement d', 'd.id = e.departement_id', 'left')
            ->join('affectation a', 'a.employe_id = e.id AND a.date_fin IS NULL', 'left')
            ->join('poste p', 'p.id = a.poste_id', 'left');

        $this->applyFilters($builder, $filters);
        $builder->orderBy('e.nom', 'ASC')->orderBy('e.prenom', 'ASC')->limit($limit, $offset);

        return $builder->get()->getResultArray();
    }

    public function countFiltered(array $filters = []): int
    {
        $builder = $this->db->table('employe e')
            ->select('COUNT(e.id) AS total')
            ->join('departement d', 'd.id = e.departement_id', 'left')
            ->join('affectation a', 'a.employe_id = e.id AND a.date_fin IS NULL', 'left')
            ->join('poste p', 'p.id = a.poste_id', 'left');

        $this->applyFilters($builder, $filters);

        $row = $builder->get()->getRowArray();

        return (int) ($row['total'] ?? 0);
    }

    public function detail(int $id): ?array
    {
        return $this->db->table('employe e')
            ->select("e.id, e.matricule, e.nom, e.prenom, e.email, e.telephone, e.adresse, e.date_naissance, e.situation_familiale, e.numero_cni, e.date_delivrance_cni, e.lieu_delivrance_cni, e.photo, e.date_embauche, e.actif, d.nom_departement AS departement, COALESCE(p.intitule, '—') AS poste_actuel")
            ->join('departement d', 'd.id = e.departement_id', 'left')
            ->join('affectation a', 'a.employe_id = e.id AND a.date_fin IS NULL', 'left')
            ->join('poste p', 'p.id = a.poste_id', 'left')
            ->where('e.id', $id)
            ->get()->getRowArray();
    }

    public function departements(): array
    {
        return $this->db->table('departement')
            ->select('id, nom_departement')
            ->orderBy('nom_departement', 'ASC')
            ->get()->getResultArray();
    }

    public function countActifs(): int
    {
        return (int) $this->db->table('employe')->where('actif', true)->countAllResults();
    }

    private function applyFilters($builder, array $filters): void
    {
        $search = trim((string) ($filters['q'] ?? ''));
        $departement = trim((string) ($filters['departement'] ?? ''));
        $statut = trim((string) ($filters['statut'] ?? ''));

        if ($search !== '') {
            $builder->groupStart();
            $builder->like('e.matricule', $search)
                ->orLike('e.nom', $search)
                ->orLike('e.prenom', $search);
            $builder->groupEnd();
        }

        if ($departement !== '') {
            $builder->where('d.id', $departement);
        }

        if ($statut !== '') {
            $builder->where('e.actif', $statut === 'actif' ? true : false);
        }
    }
}
