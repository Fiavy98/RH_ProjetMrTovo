<?php

use App\Services\Bloc5\DocumentsService;
use CodeIgniter\Test\CIUnitTestCase;

final class DocumentsServiceTest extends CIUnitTestCase
{
    public function testDocumentStateIsComputedDynamically(): void
    {
        $service = new DocumentsService();

        $this->assertSame('Valide', $service->documentState('2030-12-31'));
        $this->assertSame('Bientôt expiré', $service->documentState(date('Y-m-d', strtotime('+20 days'))));
        $this->assertSame('Expiré', $service->documentState('2020-01-01'));
        $this->assertSame('Sans expiration', $service->documentState(null));
    }

    public function testSearchEmployeesReturnsOnlyActiveEmployeesMatchingTheQuery(): void
    {
        $db = db_connect('tests');
        $db->query('DROP TABLE IF EXISTS db_employe');
        $db->query('CREATE TABLE db_employe (id INTEGER PRIMARY KEY AUTOINCREMENT, matricule TEXT, nom TEXT, prenom TEXT, actif BOOLEAN, email TEXT, telephone TEXT, adresse TEXT, situation_familiale TEXT, date_naissance TEXT, numero_cni TEXT, date_delivrance_cni TEXT, lieu_delivrance_cni TEXT, departement_id INTEGER, role_id INTEGER, candidat_id INTEGER, date_embauche TEXT)');

        $db->table('employe')->insert([
            'matricule' => 'EMP-0011',
            'nom' => 'Martin',
            'prenom' => 'Alice',
            'actif' => true,
            'email' => 'alice.martin@example.com',
            'telephone' => '0123456789',
            'adresse' => '1 rue Test',
            'situation_familiale' => 'Célibataire',
            'date_naissance' => '1990-01-15',
            'numero_cni' => 'CNI001',
            'date_delivrance_cni' => '2010-01-15',
            'lieu_delivrance_cni' => 'Paris',
            'departement_id' => 1,
            'role_id' => null,
            'candidat_id' => 1,
            'date_embauche' => '2020-01-01',
        ]);

        $db->table('employe')->insert([
            'matricule' => 'EMP-0022',
            'nom' => 'Dubois',
            'prenom' => 'Jean',
            'actif' => false,
            'email' => 'jean.dubois@example.com',
            'telephone' => '0123456789',
            'adresse' => '2 rue Test',
            'situation_familiale' => 'Marié',
            'date_naissance' => '1985-05-20',
            'numero_cni' => 'CNI002',
            'date_delivrance_cni' => '2005-05-20',
            'lieu_delivrance_cni' => 'Lyon',
            'departement_id' => 1,
            'role_id' => null,
            'candidat_id' => 2,
            'date_embauche' => '2021-02-01',
        ]);

        $service = new DocumentsService(null, null, $db);
        $results = $service->searchEmployees('EMP-0011');

        $this->assertCount(1, $results);
        $this->assertSame('EMP-0011', $results[0]['matricule']);
        $this->assertTrue((bool) $results[0]['actif']);
    }
}
