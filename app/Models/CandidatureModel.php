<?php

namespace App\Models;

use App\Entities\Candidature;

/**
 * Accès à la table `candidature`.
 * Origine : Bloc 2.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class CandidatureModel extends SirhModel
{
    protected $table      = 'candidature';
    protected $returnType = Candidature::class;

    protected $allowedFields = [
        'candidat_id',
        'offre_emploi_id',
        'lettre_motivation',
        'statut',
    ];

    protected $validationRules = [
        'candidat_id'        => 'required|is_natural_no_zero',
        'offre_emploi_id'    => 'required|is_natural_no_zero',
        'lettre_motivation'  => 'permit_empty|max_length[255]',
        'statut'             => 'permit_empty|max_length[30]',
    ];

       public function findRetenues(): array
    {
        return $this->where(
            'statut',
            Candidature::STATUT_RETENUE
        )->findAll();
    }

    public function findRetenuesAvecDetails(): array
{
    return $this->db->table('candidature c')
        ->select([
            'c.id',
            'ca.nom AS nom_candidat',
            'ca.prenom AS prenom_candidat',
            'o.titre AS titre_offre',
            'c.lettre_motivation',
            'c.statut',
            'c.date_candidature',
        ])
        ->join('candidat ca', 'ca.id = c.candidat_id')
        ->join('offre_emploi o', 'o.id = c.offre_emploi_id')
        ->join('proposition_embauche p', 'p.candidature_id = c.id', 'left')
        ->where('c.statut', \App\Entities\Candidature::STATUT_RETENUE)
        ->where('p.id', null)
        ->get()
        ->getResultArray();
}

}
