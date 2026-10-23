<?php

namespace App\Models;

use App\Entities\OffreEmploi;

/**
 * Accès à la table `offre_emploi`.
 * Origine : Bloc 2.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class OffreEmploiModel extends SirhModel
{
    protected $table      = 'offre_emploi';
    protected $returnType = OffreEmploi::class;

    protected $allowedFields = [
        'demande_recrutement_id',
        'poste_id',
        'titre',
        'description',
        'date_publication',
        'date_cloture',
        'statut',
    ];

    protected $validationRules = [
        'demande_recrutement_id'  => 'permit_empty|is_natural_no_zero',
        'poste_id'                => 'required|is_natural_no_zero',
        'titre'                   => 'required|max_length[150]',
        'description'             => 'permit_empty',
        'date_publication'        => 'permit_empty|valid_date[Y-m-d]',
        'date_cloture'            => 'permit_empty|valid_date[Y-m-d]',
        'statut'                  => 'permit_empty|max_length[30]',
    ];
}
