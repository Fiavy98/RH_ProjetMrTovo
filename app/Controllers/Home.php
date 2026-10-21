<?php

namespace App\Controllers;

use App\Models\CandidatModel;
use App\Models\ContratModel;
use App\Models\DemandeModel;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use App\Models\OnboardingModel;
use App\Models\PosteModel;

/**
 * Page d'accueil : tableau de bord minimal qui prouve que la connexion à PostgreSQL fonctionne.
 */
class Home extends BaseController
{
    public function index(): string
    {
        $stats   = [];
        $dbError = null;

        try {
            $stats = [
                ['Départements', '◫', model(DepartementModel::class)->countAllResults()],
                ['Salariés',     '♙', model(EmployeModel::class)->countAllResults()],
                ['Demandes',     '▤', model(DemandeModel::class)->countAllResults()],
                ['Postes',       '▥', model(PosteModel::class)->countAllResults()],
                ['Candidats',    '◎', model(CandidatModel::class)->countAllResults()],
                ['Contrats',     '▧', model(ContratModel::class)->countAllResults()],
                ['Onboardings',  '◴', model(OnboardingModel::class)->countAllResults()],
            ];
        } catch (\Throwable $e) {
            $dbError = ENVIRONMENT === 'development'
                ? $e->getMessage()
                : 'La base de données est inaccessible.';
        }

        return view('home', [
            'pageTitle' => 'Tableau de bord',
            'stats'     => $stats,
            'dbError'   => $dbError,
        ]);
    }
}
