<?php

namespace App\Models;

use App\Entities\HistoriqueRecrutement;

/**
 * Accès à la table `historique_recrutement`.
 * Origine : Bloc 2 (journal propre au recrutement).
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class HistoriqueRecrutementModel extends SirhModel
{
    protected $table      = 'historique_recrutement';
    protected $returnType = HistoriqueRecrutement::class;

    protected $allowedFields = [
        'entite',
        'entite_id',
        'action',
        'utilisateur_id',
        'ancienne_valeur',
        'nouvelle_valeur',
    ];

    protected $validationRules = [
        'entite'           => 'required|max_length[50]',
        'entite_id'        => 'required|is_natural_no_zero',
        'action'           => 'required|max_length[100]',
        'utilisateur_id'   => 'permit_empty|is_natural_no_zero',
        'ancienne_valeur'  => 'permit_empty',
        'nouvelle_valeur'  => 'permit_empty',
    ];
}
