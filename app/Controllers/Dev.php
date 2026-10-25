<?php

namespace App\Controllers;

use App\Models\AffectationModel;
use App\Models\CandidatModel;
use App\Models\CandidatureModel;
use App\Models\ContratModel;
use App\Models\DemandeModel;
use App\Models\DepartementModel;
use App\Models\DocumentModel;
use App\Models\EmployeModel;
use App\Models\EntretienModel;
use App\Models\EvaluationCandidatModel;
use App\Models\HistoriqueRecrutementModel;
use App\Models\OffreEmploiModel;
use App\Models\OnboardingModel;
use App\Models\OnboardingTacheModel;
use App\Models\PosteModel;
use App\Models\PropositionEmbaucheModel;
use App\Models\RoleModel;
use App\Models\StatusModel;
use App\Models\TypeContratModel;
use App\Models\TypeTacheOnboardingModel;
use App\Models\ValidationModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Outils de développement (désactivés en production) :
 *  - /dev/db   : contrôle de la connexion et des Models
 */
class Dev extends BaseController
{
    /** Table => Model, dans l'ordre de création du script SQL. */
    private const MODELS = [
        'departement'            => DepartementModel::class,
        'role'                   => RoleModel::class,
        'status'                 => StatusModel::class,
        'type_contrat'           => TypeContratModel::class,
        'employe'                => EmployeModel::class,
        'demande'                => DemandeModel::class,
        'validation'             => ValidationModel::class,
        'poste'                  => PosteModel::class,
        'offre_emploi'           => OffreEmploiModel::class,
        'candidat'               => CandidatModel::class,
        'candidature'            => CandidatureModel::class,
        'entretien'              => EntretienModel::class,
        'evaluation_candidat'    => EvaluationCandidatModel::class,
        'historique_recrutement' => HistoriqueRecrutementModel::class,
        'proposition_embauche'   => PropositionEmbaucheModel::class,
        'affectation'            => AffectationModel::class,
        'contrat'                => ContratModel::class,
        'type_tache_onboarding'  => TypeTacheOnboardingModel::class,
        'onboarding'             => OnboardingModel::class,
        'onboarding_tache'       => OnboardingTacheModel::class,
        'document'               => DocumentModel::class,
    ];

    public function db(): string
    {
        $this->bloquerEnProduction();

        $counts    = [];
        $employes  = [];
        $dbError   = null;

        try {
            foreach (self::MODELS as $table => $modelClass) {
                $counts[$table] = model($modelClass)->countAllResults();
            }
            // Lecture via une Entity : vérifie aussi les casts (dates, booléen).
            $employes = model(EmployeModel::class)->orderBy('id')->findAll(10);
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }

        return view('dev/db', [
            'pageTitle' => 'Contrôle de la base',
            'counts'    => $counts,
            'employes'  => $employes,
            'dbError'   => $dbError,
        ]);
    }

    private function bloquerEnProduction(): void
    {
        if (ENVIRONMENT === 'production') {
            throw PageNotFoundException::forPageNotFound();
        }
    }
}
