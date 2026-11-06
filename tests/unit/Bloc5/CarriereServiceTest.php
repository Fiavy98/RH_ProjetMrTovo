<?php

use App\Services\Bloc5\CarriereService;
use CodeIgniter\Test\CIUnitTestCase;

final class CarriereServiceTest extends CIUnitTestCase
{
    public function testCareerDataCanBeFetchedAndFilteredByPeriod(): void
    {
        $db = db_connect('tests');

        $db->query('DROP TABLE IF EXISTS db_historique_salaire');
        $db->query('DROP TABLE IF EXISTS db_affectation');
        $db->query('DROP TABLE IF EXISTS db_contrat');
        $db->query('DROP TABLE IF EXISTS db_poste');
        $db->query('DROP TABLE IF EXISTS db_type_contrat');
        $db->query('DROP TABLE IF EXISTS db_employe');
        $db->query('DROP TABLE IF EXISTS db_departement');

        $db->query('CREATE TABLE db_departement (id INTEGER PRIMARY KEY AUTOINCREMENT, nom_departement TEXT NOT NULL)');
        $db->query('CREATE TABLE db_employe (id INTEGER PRIMARY KEY AUTOINCREMENT, matricule TEXT NOT NULL, nom TEXT NOT NULL, prenom TEXT NOT NULL, email TEXT NOT NULL, departement_id INTEGER, actif BOOLEAN DEFAULT TRUE, date_embauche TEXT)');
        $db->query('CREATE TABLE db_type_contrat (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL, libelle TEXT NOT NULL)');
        $db->query('CREATE TABLE db_poste (id INTEGER PRIMARY KEY AUTOINCREMENT, intitule TEXT NOT NULL, departement_id INTEGER, description TEXT)');
        $db->query('CREATE TABLE db_contrat (id INTEGER PRIMARY KEY AUTOINCREMENT, employe_id INTEGER NOT NULL, type_contrat_id INTEGER NOT NULL, salaire NUMERIC(15,2) NOT NULL, date_debut TEXT NOT NULL, date_fin TEXT, date_signature TEXT, statut TEXT NOT NULL DEFAULT "signe")');
        $db->query('CREATE TABLE db_affectation (id INTEGER PRIMARY KEY AUTOINCREMENT, employe_id INTEGER NOT NULL, poste_id INTEGER NOT NULL, date_debut TEXT NOT NULL, date_fin TEXT)');
        $db->query('CREATE TABLE db_historique_salaire (id INTEGER PRIMARY KEY AUTOINCREMENT, employe_id INTEGER NOT NULL, ancien_salaire NUMERIC(15,2), nouveau_salaire NUMERIC(15,2) NOT NULL, date_effet TEXT NOT NULL, motif TEXT)');

        $db->table('departement')->insert(['nom_departement' => 'Ressources humaines']);
        $db->table('employe')->insert([
            'matricule' => 'EMP-0101',
            'nom' => 'Rajaonary',
            'prenom' => 'Miora',
            'email' => 'miora@example.com',
            'departement_id' => 1,
            'actif' => true,
            'date_embauche' => '2023-01-10',
        ]);
        $db->table('type_contrat')->insertBatch([
            ['code' => 'CDI', 'libelle' => 'Contrat à durée indéterminée'],
            ['code' => 'CDD', 'libelle' => 'Contrat à durée déterminée'],
        ]);
        $db->table('poste')->insert([
            'intitule' => 'Chargé de recrutement',
            'departement_id' => 1,
            'description' => 'Poste RH'
        ]);
        $db->table('contrat')->insertBatch([
            ['employe_id' => 1, 'type_contrat_id' => 1, 'salaire' => 2300000, 'date_debut' => '2023-01-10', 'date_fin' => null, 'date_signature' => '2023-01-08', 'statut' => 'en_cours'],
            ['employe_id' => 1, 'type_contrat_id' => 2, 'salaire' => 2150000, 'date_debut' => '2021-01-05', 'date_fin' => '2022-12-31', 'date_signature' => '2021-01-02', 'statut' => 'termine'],
        ]);
        $db->table('affectation')->insertBatch([
            ['employe_id' => 1, 'poste_id' => 1, 'date_debut' => '2023-01-10', 'date_fin' => null],
            ['employe_id' => 1, 'poste_id' => 1, 'date_debut' => '2021-01-05', 'date_fin' => '2022-12-31'],
        ]);
        $db->table('historique_salaire')->insertBatch([
            ['employe_id' => 1, 'ancien_salaire' => 2100000, 'nouveau_salaire' => 2300000, 'date_effet' => '2023-01-10', 'motif' => 'Promotion'],
            ['employe_id' => 1, 'ancien_salaire' => 2000000, 'nouveau_salaire' => 2100000, 'date_effet' => '2022-01-01', 'motif' => 'Ajustement'],
        ]);

        $service = new CarriereService($db);

        $this->assertCount(1, $service->searchEmployees('EMP-0101'));
        $this->assertCount(2, $service->contractsForEmployee(1, '2021-01-01', '2025-12-31'));
        $this->assertCount(2, $service->affectationsForEmployee(1, '2021-01-01', '2025-12-31'));
        $this->assertCount(2, $service->historiqueSalaireForEmployee(1, '2021-01-01', '2025-12-31'));
        $this->assertSame('EMP-0101', $service->employee(1)['matricule']);
    }
}
