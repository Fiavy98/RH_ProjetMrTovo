<?php

declare(strict_types=1);

use App\Services\Bloc2\RecruitmentService;
use App\Database\Migrations\CreateBloc2RecruitmentTables;
use App\Database\Migrations\CreateEntretienParticipantTable;
use CodeIgniter\Boot;
use Config\Database;
use Config\Paths;

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('ENVIRONMENT', 'testing');
define('CI_DEBUG', true);
define('HOMEPATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
define('CONFIGPATH', HOMEPATH . 'app/Config/');
define('PUBLICPATH', HOMEPATH . 'public/');
require CONFIGPATH . 'Paths.php';
$paths = new Paths();
define('APPPATH', realpath($paths->appDirectory) . DIRECTORY_SEPARATOR);
define('ROOTPATH', HOMEPATH);
define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
define('TESTPATH', HOMEPATH . 'tests/');
define('CIPATH', ROOTPATH);
define('FCPATH', PUBLICPATH);
define('SUPPORTPATH', TESTPATH . '_support/');
define('COMPOSER_PATH', '');
define('VENDORPATH', HOMEPATH . 'vendor/');
require SYSTEMPATH . 'Boot.php';
Boot::bootTest($paths);
require_once APPPATH . 'Database/Migrations/2026-10-22-100000_CreateBloc2RecruitmentTables.php';
require_once APPPATH . 'Database/Migrations/2026-10-22-110000_CreateEntretienParticipantTable.php';

$db = Database::connect([
    'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
    'foreignKeys' => true, 'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
], false);

$schema = [
    'CREATE TABLE status (id INTEGER PRIMARY KEY, code TEXT UNIQUE NOT NULL, libelle TEXT, actif INTEGER DEFAULT 1)',
    'CREATE TABLE employe (id INTEGER PRIMARY KEY, matricule TEXT, nom TEXT, prenom TEXT, actif INTEGER DEFAULT 1)',
    'CREATE TABLE demande (id INTEGER PRIMARY KEY, intitule TEXT, status_id INTEGER)',
    'CREATE TABLE validation (id INTEGER PRIMARY KEY, demande_id INTEGER, validateur_id INTEGER, status_id INTEGER, commentaire TEXT, date_validation TEXT)',
    "CREATE TABLE offre_emploi (id INTEGER PRIMARY KEY AUTOINCREMENT, demande_recrutement_id INTEGER, titre TEXT, description TEXT, date_publication TEXT, date_cloture TEXT, statut TEXT DEFAULT 'BROUILLON')",
    'CREATE TABLE candidat (id INTEGER PRIMARY KEY AUTOINCREMENT, nom TEXT, prenom TEXT, email TEXT UNIQUE, telephone TEXT, cv TEXT, date_creation TEXT DEFAULT CURRENT_TIMESTAMP)',
    "CREATE TABLE candidature (id INTEGER PRIMARY KEY AUTOINCREMENT, candidat_id INTEGER, offre_emploi_id INTEGER, date_candidature TEXT DEFAULT CURRENT_TIMESTAMP, lettre_motivation TEXT, statut TEXT DEFAULT 'EN_ATTENTE', UNIQUE(candidat_id, offre_emploi_id))",
    "CREATE TABLE entretien (id INTEGER PRIMARY KEY AUTOINCREMENT, candidature_id INTEGER, date_entretien TEXT, type_entretien TEXT, lieu TEXT, statut TEXT DEFAULT 'PLANIFIE', compte_rendu TEXT)",
    "CREATE TABLE entretien_participant (entretien_id INTEGER, employe_id INTEGER, role_participant TEXT DEFAULT 'EVALUATEUR', PRIMARY KEY(entretien_id, employe_id))",
    'CREATE TABLE evaluation_candidat (id INTEGER PRIMARY KEY AUTOINCREMENT, entretien_id INTEGER, evaluateur_id INTEGER, note NUMERIC, commentaire TEXT, decision TEXT, date_evaluation TEXT DEFAULT CURRENT_TIMESTAMP, UNIQUE(entretien_id, evaluateur_id))',
    'CREATE TABLE historique_recrutement (id INTEGER PRIMARY KEY AUTOINCREMENT, entite TEXT, entite_id INTEGER, action TEXT, utilisateur_id INTEGER, date_action TEXT DEFAULT CURRENT_TIMESTAMP, ancienne_valeur TEXT, nouvelle_valeur TEXT)',
];
foreach ($schema as $sql) { $db->query($sql); }

$db->table('status')->insertBatch([
    ['id' => 1, 'code' => 'DIRECTION_validee', 'libelle' => 'Direction validée'],
    ['id' => 2, 'code' => 'RH_validee', 'libelle' => 'RH validée'],
    ['id' => 3, 'code' => 'DIRECTION_refusee', 'libelle' => 'Direction refusée'],
]);
$db->table('employe')->insertBatch([
    ['id' => 1, 'matricule' => 'RH-1', 'nom' => 'Rakoto', 'prenom' => 'Sofia', 'actif' => 1],
    ['id' => 2, 'matricule' => 'MAN-1', 'nom' => 'Rabe', 'prenom' => 'Alain', 'actif' => 1],
    ['id' => 3, 'matricule' => 'MAN-2', 'nom' => 'Raja', 'prenom' => 'Mina', 'actif' => 1],
]);
$db->table('demande')->insertBatch([
    ['id' => 1, 'intitule' => 'Analyste', 'status_id' => 1],
    ['id' => 2, 'intitule' => 'Validation RH seulement', 'status_id' => 2],
    ['id' => 3, 'intitule' => 'Direction sans trace', 'status_id' => 1],
]);
$db->table('validation')->insertBatch([
    ['id' => 1, 'demande_id' => 1, 'validateur_id' => 2, 'status_id' => 1, 'commentaire' => 'Accord', 'date_validation' => '2026-10-20 10:00:00'],
    ['id' => 2, 'demande_id' => 2, 'validateur_id' => 1, 'status_id' => 2, 'commentaire' => 'Avis RH', 'date_validation' => '2026-10-19 10:00:00'],
]);

$service = new RecruitmentService($db);
$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (! $condition) { throw new RuntimeException("ÉCHEC #{$tests}: {$message}"); }
};

$approved = $service->approvedRequests();
$assert(array_column($approved, 'id') === [1], 'seule la demande validée et tracée par la Direction est approuvée');
$assert($service->saveOffer(['demande_recrutement_id' => 2, 'titre' => 'Interdite'])['ok'] === false, 'RH_validee ne permet pas de créer une offre');
$assert($service->saveOffer(['demande_recrutement_id' => 3, 'titre' => 'Sans validation'])['ok'] === false, 'le statut Direction sans validation ne suffit pas');
$assert($service->saveOffer(['demande_recrutement_id' => 1, 'titre' => 'Analyste', 'date_cloture' => '2026-12-31'])['ok'], 'création du brouillon approuvé');
$assert($service->publishOffer(1)['ok'], 'publication de l’offre approuvée');
$db->table('offre_emploi')->insert(['id' => 99, 'demande_recrutement_id' => 2, 'titre' => 'Brouillon RH', 'statut' => 'BROUILLON']);
$assert($service->publishOffer(99)['ok'] === false, 'publication refusée lorsque seule la validation RH existe');

$assert($service->saveCandidate(['nom' => 'Andry', 'prenom' => 'Jean', 'email' => 'jean@example.test'])['ok'], 'création candidat 1');
$assert($service->saveCandidate(['nom' => 'Raja', 'prenom' => 'Sarah', 'email' => 'sarah@example.test'])['ok'], 'création candidat 2');
$assert($service->saveApplication(['candidat_id' => 1, 'offre_emploi_id' => 1])['ok'], 'création candidature');
$assert($service->saveApplication(['candidat_id' => 1, 'offre_emploi_id' => 1])['ok'] === false, 'doublon candidat/offre rejeté');
$assert($service->changeApplicationStatus(1, ['statut' => 'PRESELECTIONNEE'])['ok'], 'présélection');

$first = ['candidature_id' => 1, 'date_entretien' => '2026-11-01T09:00', 'type_entretien' => 'RH', 'lieu' => 'Salle A', 'participant_ids' => [1, 2]];
$assert($service->saveInterview($first)['ok'], 'premier entretien planifié');
$assert($service->changeInterviewStatus(1, ['statut' => 'REALISE', 'compte_rendu' => 'Entretien RH concluant'])['ok'], 'premier entretien réalisé');
$second = ['candidature_id' => 1, 'date_entretien' => '2026-11-03T10:00', 'type_entretien' => 'TECHNIQUE', 'lieu' => 'Salle B', 'participant_ids' => [2]];
$assert($service->saveInterview($second)['ok'], 'deuxième entretien autorisé pour EN_ENTRETIEN');
$assert($service->saveInterview($second)['ok'] === false, 'deux entretiens planifiés simultanément sont refusés');
$assert($service->changeInterviewStatus(2, ['statut' => 'ANNULE'])['ok'], 'annulation historisée');
$third = ['candidature_id' => 1, 'date_entretien' => '2026-11-05T14:00', 'type_entretien' => 'TECHNIQUE', 'lieu' => 'Salle C', 'participant_ids' => [1, 2]];
$assert($service->saveInterview($third)['ok'], 'replanification après annulation');
$assert($service->changeInterviewStatus(3, ['statut' => 'REALISE', 'compte_rendu' => 'Test technique validé'])['ok'], 'entretien replanifié réalisé');

$assert($service->saveEvaluation(['entretien_id' => 3, 'evaluateur_id' => 3, 'note' => 18, 'decision' => 'FAVORABLE', 'commentaire' => 'Non affecté'])['ok'] === false, 'évaluateur non participant refusé');
$assert($service->saveEvaluation(['entretien_id' => 3, 'evaluateur_id' => 1, 'note' => 17, 'decision' => 'FAVORABLE', 'commentaire' => 'Solide'])['ok'], 'évaluation participant 1');
$assert($service->saveEvaluation(['entretien_id' => 3, 'evaluateur_id' => 2, 'note' => 15, 'decision' => 'A_REVOIR', 'commentaire' => 'À confirmer'])['ok'], 'évaluation participant 2');
$fourth = ['candidature_id' => 1, 'date_entretien' => '2026-11-07T09:00', 'type_entretien' => 'FINAL', 'lieu' => 'Direction', 'participant_ids' => [1, 2]];
$assert($service->saveInterview($fourth)['ok'], 'entretien final planifié');
$assert($service->finalDecision(1, ['decision' => 'RETENUE', 'motif' => 'Avis convergents'])['ok'] === false, 'décision finale refusée avec un entretien encore planifié');
$assert($service->changeInterviewStatus(4, ['statut' => 'ANNULE'])['ok'], 'entretien final annulé avant décision');
$assert($service->finalDecision(1, ['decision' => 'RETENUE', 'motif' => 'Avis convergents'])['ok'], 'décision finale retenue');
$assert($db->table('candidature')->where('id', 1)->get()->getRowArray()['statut'] === 'RETENUE', 'statut final persistant');
$assert($db->table('historique_recrutement')->where('action', 'ENTRETIEN_ANNULE')->countAllResults() === 2, 'les annulations sont présentes dans l’historique');
$assert($db->table('historique_recrutement')->where('action', 'ENTRETIEN_PLANIFIE')->countAllResults() === 4, 'les quatre planifications sont historisées');

$migrationDb = Database::connect([
    'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
    'foreignKeys' => true, 'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
], false);
foreach ([
    'CREATE TABLE status (id INTEGER PRIMARY KEY, code TEXT)',
    'CREATE TABLE employe (id INTEGER PRIMARY KEY, matricule TEXT, nom TEXT, prenom TEXT, actif INTEGER)',
    'CREATE TABLE demande (id INTEGER PRIMARY KEY, intitule TEXT, status_id INTEGER)',
    'CREATE TABLE validation (id INTEGER PRIMARY KEY, demande_id INTEGER, status_id INTEGER)',
    'CREATE TABLE candidat (id INTEGER PRIMARY KEY, nom TEXT, prenom TEXT, email TEXT, telephone TEXT, cv TEXT, date_creation TEXT)',
] as $sql) { $migrationDb->query($sql); }
(new CreateBloc2RecruitmentTables(Database::forge($migrationDb)))->up();
(new CreateEntretienParticipantTable(Database::forge($migrationDb)))->up();
foreach (['offre_emploi', 'candidature', 'entretien', 'evaluation_candidat', 'historique_recrutement', 'entretien_participant'] as $table) {
    $assert($migrationDb->tableExists($table), "migration additive de {$table}");
}
$offerFields = array_column($migrationDb->getFieldData('offre_emploi'), 'type', 'name');
$assert(str_contains(strtolower($offerFields['id']), 'int'), 'SQLite adapte l’identité BIGINT en identité entière compatible');

$missingDependencyDb = Database::connect([
    'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true, 'foreignKeys' => true,
], false);
$dependencyRejected = false;
try { (new CreateBloc2RecruitmentTables(Database::forge($missingDependencyDb)))->up(); } catch (RuntimeException) { $dependencyRejected = true; }
$assert($dependencyRejected, 'la migration refuse de démarrer sans les tables partagées du Bloc 1');

$menu = require APPPATH . 'Config/Menu/bloc2.php';
$assert(array_reduce($menu['items'], static fn (bool $ok, array $item): bool => $ok && ($item['roles'] ?? []) === ['rh'], true), 'le rôle admin n’hérite pas du menu RH');

echo "Bloc 2: {$tests} assertions réussies." . PHP_EOL;
