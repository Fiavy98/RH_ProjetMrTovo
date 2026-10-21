<?php

namespace App\Services\Bloc3;

use App\Services\BlocPageService;

/** Écrans de démonstration du Bloc 3 : proposition, validation et embauche. */
class Bloc3PageService extends BlocPageService
{
    public function screens(): array
    {
        return $this->defineScreens([
            'dashboard' => 'Suivi des embauches', 'selected' => 'Candidats retenus',
            'proposal-form' => 'Proposition d’embauche', 'responses' => 'Réponses des candidats',
            'hire-validations' => 'Validation Direction', 'employees' => 'Dossiers salariés',
            'assignment-b3' => 'Affectations initiales', 'handoff' => 'Transmission au Bloc 4',
        ], 'Bloc 3 · Sélection et embauche');
    }
}
