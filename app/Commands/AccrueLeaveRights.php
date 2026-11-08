<?php

namespace App\Commands;

use App\Services\Bloc7\DroitCongeService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/** Tâche idempotente à planifier quotidiennement via le scheduler système. */
class AccrueLeaveRights extends BaseCommand
{
    protected $group = 'Congés';
    protected $name = 'conges:acquerir';
    protected $description = 'Génère les tranches mensuelles acquises et recalcule les soldes.';
    protected $usage = 'conges:acquerir';

    public function run(array $params)
    {
        $db = db_connect();
        $service = new DroitCongeService($db);
        $employees = $db->table('employe')->select('id')->get()->getResultArray();
        $count = 0;

        foreach ($employees as $employee) {
            try {
                $service->synchroniser((int) $employee['id']);
                $count++;
            } catch (Throwable $exception) {
                CLI::error('Employé ' . $employee['id'] . ' : ' . $exception->getMessage());
            }
        }

        CLI::write($count . ' dossiers salariés synchronisés.');
    }
}
