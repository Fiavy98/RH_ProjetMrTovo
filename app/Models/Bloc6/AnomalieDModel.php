<?php

namespace App\Models\Bloc6;

use App\Models\SirhModel;

class AnomalieDModel extends SirhModel
{
    protected $table = 'anomalie_pointage';
    protected $returnType = 'array';
    protected $allowedFields = [
        'employe_id', 'planning_id', 'pointage_id', 'date_anomalie', 'type',
        'detail', 'statut', 'motif_traitement', 'detectee_le', 'traitee_le',
        'traitee_par', 'derniere_detection_le',
    ];
}
