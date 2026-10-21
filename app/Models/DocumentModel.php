<?php

namespace App\Models;

use App\Entities\Document;

/**
 * Accès à la table `document`.
 * Origine : Bloc 4.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class DocumentModel extends SirhModel
{
    protected $table      = 'document';
    protected $returnType = Document::class;

    protected $allowedFields = [
        'employe_id',
        'contrat_id',
        'type_document',
        'nom_fichier',
        'chemin_fichier',
        'type_mime',
        'taille_octets',
        'cree_par',
    ];

    protected $validationRules = [
        'employe_id'      => 'required|is_natural_no_zero',
        'contrat_id'      => 'permit_empty|is_natural_no_zero',
        'type_document'   => 'required|in_list[contrat_signe,piece_identite,autre]',
        'nom_fichier'     => 'required|max_length[255]',
        'chemin_fichier'  => 'required|max_length[500]',
        'type_mime'       => 'permit_empty|max_length[100]',
        'taille_octets'   => 'permit_empty|is_natural',
        'cree_par'        => 'permit_empty|is_natural_no_zero',
    ];
}
