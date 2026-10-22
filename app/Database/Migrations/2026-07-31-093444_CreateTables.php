<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom_departement' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nom_departement');
        $this->forge->createTable('departement');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nom_role');
        $this->forge->createTable('role');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'libelle' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'actif' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('status');

        // EmployeModel references candidat_id, so this table is required as well.
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'prenom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'telephone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'cv' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'date_creation' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('candidat');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'matricule' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'prenom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'departement_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'role_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'candidat_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'date_embauche' => [
                'type' => 'DATE',
            ],
            'actif' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('matricule');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('employe');
        $this->forge->addForeignKey('departement_id', 'departement', 'id', '', '', 'fk_employe_departement');
        $this->forge->addForeignKey('role_id', 'role', 'id', '', '', 'fk_employe_role');
        $this->forge->addForeignKey('candidat_id', 'candidat', 'id', '', '', 'fk_employe_candidat');
        $this->forge->processIndexes('employe');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'date_demande' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'intitule' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'nombre_postes' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'motif' => [
                'type' => 'TEXT',
            ],
            'budget_min' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'budget_max' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'status_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'demandeur_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'departement_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('demande');
        $this->forge->addForeignKey('status_id', 'status', 'id', '', '', 'fk_demande_status');
        $this->forge->addForeignKey('demandeur_id', 'employe', 'id', '', '', 'fk_demande_demandeur');
        $this->forge->addForeignKey('departement_id', 'departement', 'id', '', '', 'fk_demande_departement');
        $this->forge->processIndexes('demande');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'demande_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'validateur_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'status_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'commentaire' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'date_validation' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('validation');
        $this->forge->addForeignKey('demande_id', 'demande', 'id', '', 'CASCADE', 'fk_validation_demande');
        $this->forge->addForeignKey('validateur_id', 'employe', 'id', '', '', 'fk_validation_validateur');
        $this->forge->addForeignKey('status_id', 'status', 'id', '', '', 'fk_validation_status');
        $this->forge->processIndexes('validation');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'intitule' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'competences_requises' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'demande_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'departement_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'status_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('poste');
        $this->forge->addForeignKey('demande_id', 'demande', 'id', '', '', 'fk_poste_demande');
        $this->forge->addForeignKey('departement_id', 'departement', 'id', '', '', 'fk_poste_departement');
        $this->forge->addForeignKey('status_id', 'status', 'id', '', '', 'fk_poste_status');
        $this->forge->processIndexes('poste');
    }

    public function down()
    {
        $this->forge->dropTable('poste', true);
        $this->forge->dropTable('validation', true);
        $this->forge->dropTable('demande', true);
        $this->forge->dropTable('employe', true);
        $this->forge->dropTable('candidat', true);
        $this->forge->dropTable('status', true);
        $this->forge->dropTable('role', true);
        $this->forge->dropTable('departement', true);
    }
}
