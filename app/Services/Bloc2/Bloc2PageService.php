<?php

namespace App\Services\Bloc2;

use App\Services\BlocPageService;

/** Écrans de démonstration du Bloc 2 : offres, candidatures et entretiens. */
class Bloc2PageService extends BlocPageService
{
    public function screens(): array
    {
        return $this->defineScreens([
            'dashboard' => 'Suivi du recrutement', 'offers' => 'Offres d’emploi', 'offer-form' => 'Créer / modifier une offre',
            'candidates' => 'Candidats', 'candidate-form' => 'Nouveau candidat', 'candidate-detail' => 'Fiche candidat',
            'applications' => 'Candidatures', 'application-form' => 'Nouvelle candidature', 'preselection' => 'Présélection',
            'interviews' => 'Entretiens', 'interview-form' => 'Planifier un entretien',
            'evaluations' => 'Évaluations', 'evaluation-form' => 'Évaluer un entretien',
            'decisions' => 'Décision finale', 'recruitment-history' => 'Historique recrutement',
        ], 'Bloc 2 · Recrutement');
    }
}
