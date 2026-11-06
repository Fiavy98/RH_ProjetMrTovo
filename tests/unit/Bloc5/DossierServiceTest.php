<?php

use App\Models\EmployeModel;
use App\Services\Bloc5\DossierService;
use CodeIgniter\Test\CIUnitTestCase;

final class DossierServiceTest extends CIUnitTestCase
{
    public function testEmployeeDetailUsesRealEmployeeData(): void
    {
        $model = $this->getMockBuilder(EmployeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['detail'])
            ->getMock();

        $model->method('detail')->with(42)->willReturn([
            'id' => 42,
            'matricule' => 'RH-042',
            'nom' => 'Dupont',
            'prenom' => 'Alice',
            'email' => 'alice.dupont@example.com',
            'telephone' => '0123456789',
            'adresse' => '12 rue de l\'Industrie, Paris',
            'date_naissance' => '1990-05-22',
            'situation_familiale' => 'Célibataire',
            'numero_cni' => 'AB123456',
            'date_delivrance_cni' => '2012-06-15',
            'lieu_delivrance_cni' => 'Paris',
            'photo' => '',
            'departement' => 'Ressources Humaines',
            'poste_actuel' => 'Responsable RH',
            'date_embauche' => '2022-01-10',
            'actif' => 1,
        ]);

        $service = new DossierService($model);
        $employee = $service->employee(42);

        $this->assertSame('Dupont', $employee['nom']);
        $this->assertSame('Ressources Humaines', $employee['departement']);
        $this->assertSame('Responsable RH', $employee['poste_actuel']);
        $this->assertSame('assets/images/default-avatar.png', $employee['photo']);
        $this->assertSame('10/01/2022', $employee['date_embauche']);
        $this->assertSame('Actif', $employee['statut']);
    }
}
