<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/** Association explicite entre un entretien et ses participants autorisés. */
class CreateEntretienParticipantTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('entretien_participant')) {
            $columns = array_map(static fn ($field): string => $field->name, $this->db->getFieldData('entretien_participant'));
            $missing = array_diff(['entretien_id', 'employe_id', 'role_participant'], $columns);
            if ($missing !== []) {
                throw new RuntimeException('La table `entretien_participant` est incomplète : ' . implode(', ', $missing));
            }
            return;
        }
        foreach (['entretien', 'employe'] as $table) {
            if (! $this->db->tableExists($table)) {
                throw new RuntimeException("La table `{$table}` est requise avant `entretien_participant`.");
            }
        }

        $this->forge->addField([
            'entretien_id' => ['type' => $this->idTypeFor('entretien')],
            'employe_id' => ['type' => $this->idTypeFor('employe')],
            'role_participant' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'EVALUATEUR'],
        ]);
        $this->forge->addField("CONSTRAINT chk_entretien_participant_role CHECK (role_participant IN ('EVALUATEUR','OBSERVATEUR'))");
        $this->forge->addKey(['entretien_id', 'employe_id'], true);
        $this->forge->addKey('employe_id');
        $this->forge->addForeignKey('entretien_id', 'entretien', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('employe_id', 'employe', 'id', '', 'CASCADE');
        $this->forge->createTable('entretien_participant');
    }

    public function down()
    {
        // Aucune suppression automatique d'affectations RH.
    }

    private function idTypeFor(string $table): string
    {
        foreach ($this->db->getFieldData($table) as $field) {
            if ($field->name === 'id') {
                $type = strtolower((string) $field->type);
                return str_contains($type, 'big') || str_contains($type, 'int8') ? 'BIGINT' : 'INT';
            }
        }
        throw new RuntimeException("La table `{$table}` ne possède pas d’identifiant `id`.");
    }
}
