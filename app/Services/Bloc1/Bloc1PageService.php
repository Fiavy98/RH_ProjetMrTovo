<?php

namespace App\Services\Bloc1;

use App\Services\BlocPageService;

/** Écrans de démonstration du Bloc 1 : expression du besoin et validations. */
class Bloc1PageService extends BlocPageService
{
    public function screens(): array
    {
        return $this->defineScreens([
            'dashboard' => 'Vue des besoins', 'requests' => 'Mes demandes', 'request-form' => 'Nouvelle demande',
            'request-check' => 'Consulter une demande', 'hierarchy' => 'Examen hiérarchique',
            'rh-review' => 'Analyse RH', 'finance-review' => 'Avis Finance',
            'direction-review' => 'Décisions Direction', 'positions' => 'Postes associés',
            'request-history' => 'Historique des demandes',
        ], 'Bloc 1 · Expression du besoin');
    }
}
