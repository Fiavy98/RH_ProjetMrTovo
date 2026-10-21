<?php

namespace App\Models;

use App\Entities\TypeContrat;

/**
 * Accès à la table `type_contrat`.
 * Origine : Référentiel partagé (Blocs 3 et 4) : table de paramètres des contrats.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class TypeContratModel extends SirhModel
{
    protected $table      = 'type_contrat';
    protected $returnType = TypeContrat::class;

    protected $allowedFields = [
        'code',
        'libelle',
        'duree_max_mois',
        'nb_renouvellements_max',
    ];

    protected $validationRules = [
        'code'                    => 'required|max_length[10]',
        'libelle'                 => 'required|max_length[60]',
        'duree_max_mois'          => 'permit_empty|is_natural_no_zero',
        'nb_renouvellements_max'  => 'required|is_natural',
    ];
}
