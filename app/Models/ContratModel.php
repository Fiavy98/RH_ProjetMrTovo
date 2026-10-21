<?php

namespace App\Models;

use App\Entities\Contrat;

/**
 * Accès à la table `contrat`.
 * Origine : Bloc 4.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class ContratModel extends SirhModel
{
    protected $table      = 'contrat';
    protected $returnType = Contrat::class;

    protected $allowedFields = [
        'employe_id',
        'type_contrat_id',
        'contrat_precedent_id',
        'salaire',
        'date_debut',
        'date_fin',
        'date_signature',
        'statut',
        'cree_par',
    ];

    protected $validationRules = [
        'employe_id'            => 'required|is_natural_no_zero',
        'type_contrat_id'       => 'required|is_natural_no_zero',
        'contrat_precedent_id'  => 'permit_empty|is_natural_no_zero',
        'salaire'               => 'required|numeric|greater_than_equal_to[0]',
        'date_debut'            => 'required|valid_date[Y-m-d]',
        'date_fin'              => 'permit_empty|valid_date[Y-m-d]',
        'date_signature'        => 'permit_empty|valid_date[Y-m-d]',
        'statut'                => 'permit_empty|in_list[brouillon,signe,en_cours,termine,annule]',
        'cree_par'              => 'permit_empty|is_natural_no_zero',
    ];
}
