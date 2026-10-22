<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeContratToDemande extends Migration
{
    public function up()
    {
        $this->forge->addColumn('demande', [
            'type_contrat_id' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
        ]);

        $this->forge->addForeignKey(
            'type_contrat_id',
            'type_contrat',
            'id',
            '',
            '',
            'fk_demande_type_contrat'
        );
        $this->forge->processIndexes('demande');
    }

    public function down()
    {
        $this->forge->dropForeignKey('demande', 'fk_demande_type_contrat');
        $this->forge->dropColumn('demande', 'type_contrat_id');
    }
}
