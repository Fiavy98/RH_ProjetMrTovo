<?php

declare(strict_types=1);

use App\Database\Migrations\AddTypeContratToDemande;
use App\Database\Migrations\UpdateDemandePoste;
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
require_once APPPATH . 'Database/Migrations/2026-10-22-000001_UpdateDemandePoste.php';
require_once APPPATH . 'Database/Migrations/2026-10-22-000002_AddTypeContratToDemande.php';

$connect = static fn () => Database::connect([
    'DBDriver' => 'SQLite3',
    'database' => ':memory:',
    'DBPrefix' => '',
    'DBDebug' => true,
    'foreignKeys' => true,
], false);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (! $condition) {
        throw new RuntimeException("ÉCHEC migration commune #{$tests}: {$message}");
    }
};

// Schéma neuf : les colonnes demande sont déjà présentes et poste ne possède
// déjà plus nombre_postes.
$fresh = $connect();
$fresh->query('CREATE TABLE demande (id INTEGER PRIMARY KEY, intitule VARCHAR(150), nombre_postes INTEGER DEFAULT 1)');
$fresh->query('CREATE TABLE poste (id INTEGER PRIMARY KEY, demande_id INTEGER NOT NULL)');
$fresh->query('CREATE INDEX idx_poste_demande_id ON poste(demande_id)');
$freshUpdate = new UpdateDemandePoste(Database::forge($fresh));
$freshUpdate->up();
$freshUpdate->up();
$assert($fresh->fieldExists('intitule', 'demande'), 'intitule conservé sur base neuve');
$assert($fresh->fieldExists('nombre_postes', 'demande'), 'nombre_postes conservé sur base neuve');
$assert(! $fresh->fieldExists('nombre_postes', 'poste'), 'poste reste sans nombre_postes');
$assert(isset($fresh->getIndexData('poste')['idx_poste_demande_id']), 'index normal demande_id conservé');

$freshContract = new AddTypeContratToDemande(Database::forge($fresh));
$freshContract->up();
$freshContract->up();
$assert($fresh->tableExists('type_contrat'), 'référentiel type_contrat créé si absent');
$assert($fresh->fieldExists('type_contrat_id', 'demande'), 'clé type_contrat ajoutée à demande');
$assert(count($fresh->getForeignKeyData('demande')) === 1, 'clé étrangère créée une seule fois');

// Ancien schéma : migration des deux colonnes depuis poste, suppression de
// l'ancienne unicité et réutilisation d'un référentiel existant en INT.
$legacy = $connect();
$legacy->query('CREATE TABLE demande (id INTEGER PRIMARY KEY)');
$legacy->query('CREATE TABLE poste (id INTEGER PRIMARY KEY, demande_id INTEGER NOT NULL, nombre_postes INTEGER DEFAULT 1)');
$legacy->query('CREATE UNIQUE INDEX uq_poste_demande_id ON poste(demande_id)');
$legacy->query('CREATE TABLE type_contrat (id INTEGER PRIMARY KEY, code VARCHAR(10) UNIQUE, libelle VARCHAR(60), duree_max_mois INTEGER, nb_renouvellements_max INTEGER DEFAULT 0)');
$legacyUpdate = new UpdateDemandePoste(Database::forge($legacy));
$legacyUpdate->up();
$assert($legacy->fieldExists('intitule', 'demande'), 'intitule ajouté à l’ancien schéma');
$assert($legacy->fieldExists('nombre_postes', 'demande'), 'nombre_postes ajouté à demande');
$assert(! $legacy->fieldExists('nombre_postes', 'poste'), 'ancien poste.nombre_postes supprimé');
$uniqueDemande = array_filter(
    $legacy->getIndexData('poste'),
    static fn ($index): bool => $index->type === 'UNIQUE' && $index->fields === ['demande_id']
);
$assert($uniqueDemande === [], 'ancienne unicité poste.demande_id supprimée');

$legacyContract = new AddTypeContratToDemande(Database::forge($legacy));
$legacyContract->up();
$legacyContract->up();
$assert($legacy->fieldExists('type_contrat_id', 'demande'), 'référentiel existant réutilisé');
$assert(count($legacy->getForeignKeyData('demande')) === 1, 'clé étrangère existante non dupliquée');

echo "Migrations communes : {$tests} assertions réussies." . PHP_EOL;
