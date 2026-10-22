<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateDemandePoste extends Migration
{
    public function up()
    {
        $this->forge->addColumn('demande', [
            'intitule' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'default'    => '',
            ],
            'nombre_postes' => [
                'type'    => 'INT',
                'default' => 1,
            ],
        ]);

        $this->forge->dropColumn('poste', 'nombre_postes');
        $this->forge->dropKey('poste', 'demande_id');
    }

    public function down()
    {
        $this->forge->addColumn('poste', [
            'nombre_postes' => [
                'type'    => 'INT',
                'default' => 1,
            ],
        ]);
        $this->forge->addUniqueKey('demande_id');
        $this->forge->dropColumn('demande', ['intitule', 'nombre_postes']);
    }
}
