<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
use RuntimeException;

/** Migration additive du Bloc 2, compatible avec les identifiants du schéma partagé. */
class CreateBloc2RecruitmentTables extends Migration
{
    public function up()
    {
        $this->assertSharedDependencies();

        $demandeIdType = $this->idTypeFor('demande');
        $employeeIdType = $this->idTypeFor('employe');

        if (! $this->db->tableExists('offre_emploi')) {
            $this->forge->addField([
                'id' => $this->identity(),
                'demande_recrutement_id' => ['type' => $demandeIdType],
                'titre' => ['type' => 'VARCHAR', 'constraint' => 150],
                'description' => ['type' => 'TEXT', 'null' => true],
                'date_publication' => ['type' => 'DATE', 'null' => true],
                'date_cloture' => ['type' => 'DATE', 'null' => true],
                'statut' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'BROUILLON'],
            ]);
            $this->forge->addField("CONSTRAINT chk_offre_statut CHECK (statut IN ('BROUILLON','PUBLIEE','CLOTUREE'))");
            $this->forge->addField('CONSTRAINT chk_offre_dates CHECK (date_cloture IS NULL OR date_publication IS NULL OR date_cloture >= date_publication)');
            $this->forge->addKey('id', true);
            $this->forge->addKey('demande_recrutement_id');
            $this->forge->addForeignKey('demande_recrutement_id', 'demande', 'id', '', 'RESTRICT');
            $this->forge->createTable('offre_emploi');
        }
        $this->assertColumns('offre_emploi', ['id', 'demande_recrutement_id', 'titre', 'description', 'date_publication', 'date_cloture', 'statut']);

        if (! $this->db->tableExists('candidat')) {
            $this->forge->addField([
                'id' => $this->identity(),
                'nom' => ['type' => 'VARCHAR', 'constraint' => 100],
                'prenom' => ['type' => 'VARCHAR', 'constraint' => 100],
                'email' => ['type' => 'VARCHAR', 'constraint' => 150],
                'telephone' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'cv' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'date_creation' => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('email');
            $this->forge->createTable('candidat');
        } else {
            $this->assertColumns('candidat', ['id', 'nom', 'prenom', 'email', 'telephone', 'cv', 'date_creation']);
        }

        $candidateIdType = $this->idTypeFor('candidat');
        $offerIdType = $this->idTypeFor('offre_emploi');

        if (! $this->db->tableExists('candidature')) {
            $this->forge->addField([
                'id' => $this->identity(),
                'candidat_id' => ['type' => $candidateIdType],
                'offre_emploi_id' => ['type' => $offerIdType],
                'date_candidature' => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
                'lettre_motivation' => ['type' => 'TEXT', 'null' => true],
                'statut' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'EN_ATTENTE'],
            ]);
            $this->forge->addField("CONSTRAINT chk_candidature_statut CHECK (statut IN ('EN_ATTENTE','PRESELECTIONNEE','EN_ENTRETIEN','RETENUE','REJETEE'))");
            $this->forge->addKey('id', true);
            $this->forge->addKey('candidat_id');
            $this->forge->addKey('offre_emploi_id');
            $this->forge->addUniqueKey(['candidat_id', 'offre_emploi_id']);
            $this->forge->addForeignKey('candidat_id', 'candidat', 'id', '', 'RESTRICT');
            $this->forge->addForeignKey('offre_emploi_id', 'offre_emploi', 'id', '', 'RESTRICT');
            $this->forge->createTable('candidature');
        }
        $this->assertColumns('candidature', ['id', 'candidat_id', 'offre_emploi_id', 'date_candidature', 'lettre_motivation', 'statut']);

        $applicationIdType = $this->idTypeFor('candidature');
        if (! $this->db->tableExists('entretien')) {
            $this->forge->addField([
                'id' => $this->identity(),
                'candidature_id' => ['type' => $applicationIdType],
                'date_entretien' => ['type' => 'TIMESTAMP'],
                'type_entretien' => ['type' => 'VARCHAR', 'constraint' => 50],
                'lieu' => ['type' => 'VARCHAR', 'constraint' => 150],
                'statut' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'PLANIFIE'],
                'compte_rendu' => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addField("CONSTRAINT chk_entretien_type CHECK (type_entretien IN ('RH','TECHNIQUE','FINAL','TEST_TECHNIQUE'))");
            $this->forge->addField("CONSTRAINT chk_entretien_statut CHECK (statut IN ('PLANIFIE','REALISE','ANNULE'))");
            $this->forge->addKey('id', true);
            $this->forge->addKey('candidature_id');
            $this->forge->addForeignKey('candidature_id', 'candidature', 'id', '', 'CASCADE');
            $this->forge->createTable('entretien');
        }
        $this->assertColumns('entretien', ['id', 'candidature_id', 'date_entretien', 'type_entretien', 'lieu', 'statut', 'compte_rendu']);

        $interviewIdType = $this->idTypeFor('entretien');
        if (! $this->db->tableExists('evaluation_candidat')) {
            $this->forge->addField([
                'id' => $this->identity(),
                'entretien_id' => ['type' => $interviewIdType],
                'evaluateur_id' => ['type' => $employeeIdType, 'null' => true],
                'note' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'commentaire' => ['type' => 'TEXT', 'null' => true],
                'decision' => ['type' => 'VARCHAR', 'constraint' => 30],
                'date_evaluation' => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            ]);
            $this->forge->addField('CONSTRAINT chk_evaluation_note CHECK (note IS NULL OR (note >= 0 AND note <= 20))');
            $this->forge->addField("CONSTRAINT chk_evaluation_decision CHECK (decision IN ('FAVORABLE','DEFAVORABLE','A_REVOIR'))");
            $this->forge->addKey('id', true);
            $this->forge->addKey('entretien_id');
            $this->forge->addKey('evaluateur_id');
            $this->forge->addUniqueKey(['entretien_id', 'evaluateur_id']);
            $this->forge->addForeignKey('entretien_id', 'entretien', 'id', '', 'CASCADE');
            $this->forge->addForeignKey('evaluateur_id', 'employe', 'id', '', 'RESTRICT');
            $this->forge->createTable('evaluation_candidat');
        }
        $this->assertColumns('evaluation_candidat', ['id', 'entretien_id', 'evaluateur_id', 'note', 'commentaire', 'decision', 'date_evaluation']);

        if (! $this->db->tableExists('historique_recrutement')) {
            $this->forge->addField([
                'id' => $this->identity(),
                'entite' => ['type' => 'VARCHAR', 'constraint' => 50],
                'entite_id' => ['type' => 'BIGINT'],
                'action' => ['type' => 'VARCHAR', 'constraint' => 100],
                'utilisateur_id' => ['type' => $employeeIdType, 'null' => true],
                'date_action' => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')],
                'ancienne_valeur' => ['type' => 'TEXT', 'null' => true],
                'nouvelle_valeur' => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['entite', 'entite_id']);
            $this->forge->addKey('date_action');
            $this->forge->addForeignKey('utilisateur_id', 'employe', 'id', '', 'SET NULL');
            $this->forge->createTable('historique_recrutement');
        }
        $this->assertColumns('historique_recrutement', ['id', 'entite', 'entite_id', 'action', 'utilisateur_id', 'date_action', 'ancienne_valeur', 'nouvelle_valeur']);
    }

    public function down()
    {
        // Aucune suppression automatique de données RH.
    }

    private function assertSharedDependencies(): void
    {
        foreach (['demande', 'validation', 'status', 'employe'] as $table) {
            if (! $this->db->tableExists($table)) {
                throw new RuntimeException("La migration Bloc 2 dépend de la table partagée `{$table}`. Fusionnez et migrez d’abord le Bloc 1.");
            }
        }
        $this->assertColumns('demande', ['id', 'intitule', 'status_id']);
        $this->assertColumns('validation', ['demande_id', 'status_id']);
        $this->assertColumns('status', ['id', 'code']);
        $this->assertColumns('employe', ['id', 'nom', 'prenom', 'matricule', 'actif']);
    }

    private function assertColumns(string $table, array $required): void
    {
        $columns = array_map(static fn ($field): string => $field->name, $this->db->getFieldData($table));
        $missing = array_diff($required, $columns);
        if ($missing !== []) {
            throw new RuntimeException("La table `{$table}` ne correspond pas au schéma attendu. Colonnes manquantes : " . implode(', ', $missing));
        }
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

    private function identity(): array
    {
        return ['type' => 'BIGINT', 'auto_increment' => true];
    }
}
