<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class UpdateDemandePoste extends Migration
{
    public function up()
    {
        foreach (['demande', 'poste'] as $table) {
            if (! $this->db->tableExists($table)) {
                throw new RuntimeException("La table `{$table}` doit exister avant UpdateDemandePoste.");
            }
        }

        if (! $this->db->fieldExists('intitule', 'demande')) {
            $this->forge->addColumn('demande', ['intitule' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'default'    => '',
            ]]);
        }

        if (! $this->db->fieldExists('nombre_postes', 'demande')) {
            $this->forge->addColumn('demande', ['nombre_postes' => [
                'type'    => 'INT',
                'default' => 1,
            ]]);
        }

        // Les anciens schémas pouvaient imposer un poste unique par demande.
        // On retire uniquement cette contrainte UNIQUE, jamais l'index normal
        // ni la clé étrangère nécessaires à poste.demande_id.
        foreach ($this->db->getIndexData('poste') as $name => $index) {
            if ($index->type === 'UNIQUE' && $index->fields === ['demande_id']) {
                $this->forge->dropKey('poste', (string) $name, false);
            }
        }

        if ($this->db->fieldExists('nombre_postes', 'poste')) {
            $this->forge->dropColumn('poste', 'nombre_postes');
        }
    }

    public function down()
    {
        // Migration de compatibilité : ne pas supprimer les colonnes désormais
        // possédées par le schéma initial, ni réintroduire une unicité obsolète.
    }
}
