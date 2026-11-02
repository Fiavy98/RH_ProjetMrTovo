<?php

namespace App\Models\Bloc5;

use App\Models\SirhModel;

/** Modèle à raccorder au schéma des documents administratifs du Bloc 5. */
class DocumentAdministratifModel extends SirhModel
{
    // TODO Bloc 5 : confirmer le nom de la table et les champs après conception du schéma.
    protected $table = 'document_administratif';
    protected $allowedFields = [];
}
