<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddTypeContratToDemande extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('demande')) {
            throw new RuntimeException('La table `demande` doit exister avant AddTypeContratToDemande.');
        }

        // Référentiel partagé attendu par les Blocs 1, 3 et 4. Une table déjà
        // fournie par le schéma PostgreSQL de l'équipe est conservée intacte.
        if (! $this->db->tableExists('type_contrat')) {
            $this->forge->addField([
                'id' => ['type' => 'BIGINT', 'auto_increment' => true],
                'code' => ['type' => 'VARCHAR', 'constraint' => 10],
                'libelle' => ['type' => 'VARCHAR', 'constraint' => 60],
                'duree_max_mois' => ['type' => 'INT', 'null' => true],
                'nb_renouvellements_max' => ['type' => 'INT', 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('code');
            $this->forge->createTable('type_contrat');
        }

        foreach (['id', 'code', 'libelle', 'duree_max_mois', 'nb_renouvellements_max'] as $field) {
            if (! $this->db->fieldExists($field, 'type_contrat')) {
                throw new RuntimeException("La table `type_contrat` est incompatible : colonne `{$field}` absente.");
            }
        }

        $typeContratIdType = $this->idTypeFor('type_contrat');
        if (! $this->db->fieldExists('type_contrat_id', 'demande')) {
            $this->forge->addColumn('demande', [
                'type_contrat_id' => [
                    'type' => $typeContratIdType,
                    'null' => true,
                ],
            ]);
        } elseif ($this->integerTypeForField('demande', 'type_contrat_id') !== $typeContratIdType) {
            throw new RuntimeException('Le type de demande.type_contrat_id est incompatible avec type_contrat.id.');
        }

        if (! $this->hasTypeContratForeignKey()) {
            $foreignKeyName = $this->db->DBDriver === 'SQLite3' ? '' : 'fk_demande_type_contrat';
            $this->forge->addForeignKey(
                'type_contrat_id',
                'type_contrat',
                'id',
                '',
                '',
                $foreignKeyName
            );
            $this->forge->processIndexes('demande');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('demande')) {
            return;
        }

        if ($this->hasForeignKey('demande', 'fk_demande_type_contrat')) {
            $this->forge->dropForeignKey('demande', 'fk_demande_type_contrat');
        }
        if ($this->db->fieldExists('type_contrat_id', 'demande')) {
            $this->forge->dropColumn('demande', 'type_contrat_id');
        }
        // `type_contrat` est partagée avec les Blocs 3 et 4 : ne pas la supprimer.
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        foreach ($this->db->getForeignKeyData($table) as $key => $foreignKey) {
            if ($key === $name || ($foreignKey->constraint_name ?? null) === $name) {
                return true;
            }
        }

        return false;
    }

    private function hasTypeContratForeignKey(): bool
    {
        foreach ($this->db->getForeignKeyData('demande') as $foreignKey) {
            if (($foreignKey->column_name ?? []) === ['type_contrat_id']
                && ($foreignKey->foreign_table_name ?? null) === 'type_contrat'
                && ($foreignKey->foreign_column_name ?? []) === ['id']) {
                return true;
            }
        }

        return false;
    }

    private function idTypeFor(string $table): string
    {
        return $this->integerTypeForField($table, 'id');
    }

    private function integerTypeForField(string $table, string $column): string
    {
        foreach ($this->db->getFieldData($table) as $field) {
            if ($field->name === $column) {
                $type = strtolower((string) $field->type);

                return str_contains($type, 'big') || str_contains($type, 'int8') ? 'BIGINT' : 'INT';
            }
        }

        throw new RuntimeException("La colonne `{$table}.{$column}` est absente.");
    }
}
