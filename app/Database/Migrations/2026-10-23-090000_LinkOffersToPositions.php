<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/** Lie chaque offre de recrutement à un poste précis, sans modifier le Bloc 1. */
class LinkOffersToPositions extends Migration
{
    public function up()
    {
        foreach (['offre_emploi', 'poste'] as $table) {
            if (! $this->db->tableExists($table)) {
                throw new RuntimeException("La table `{$table}` doit exister avant LinkOffersToPositions.");
            }
        }

        if (! $this->db->fieldExists('poste_id', 'offre_emploi')) {
            $this->forge->addColumn('offre_emploi', [
                'poste_id' => [
                    'type' => $this->idTypeFor('poste'),
                    'null' => true,
                ],
            ]);
            $this->db->resetDataCache();
        }

        // La colonne reste nullable pour ne pas invalider les anciennes offres.
        // Le service exige toutefois un poste pour toute création, modification
        // ou publication effectuée après cette migration.
        if (! $this->db->fieldExists('poste_id', 'offre_emploi')) {
            throw new RuntimeException('La colonne `offre_emploi.poste_id` n’a pas pu être ajoutée.');
        }

        // Rattrapage sans ambiguïté des anciennes offres : si la demande n'a
        // qu'un seul poste, ce poste est nécessairement celui de l'offre.
        // Lorsqu'il existe plusieurs postes, aucune affectation n'est devinée.
        $legacyOffers = $this->db->table('offre_emploi')->select('id, demande_recrutement_id')
            ->where('poste_id IS NULL', null, false)->get()->getResultArray();
        foreach ($legacyOffers as $offer) {
            $positions = $this->db->table('poste')->select('id')->where('demande_id', $offer['demande_recrutement_id'])->limit(2)->get()->getResultArray();
            if (count($positions) === 1) {
                $this->db->table('offre_emploi')->where('id', $offer['id'])->update(['poste_id' => $positions[0]['id']]);
            }
        }

        if ($this->db->DBDriver === 'Postgre') {
            $orphans = $this->db->table('offre_emploi o')->join('poste p', 'p.id = o.poste_id', 'left')
                ->where('o.poste_id IS NOT NULL', null, false)->where('p.id IS NULL', null, false)->countAllResults();
            if ($orphans > 0) {
                throw new RuntimeException('Des offres existantes référencent un poste inexistant. Corrigez-les avant de créer la clé étrangère.');
            }
            $this->db->query('CREATE INDEX IF NOT EXISTS idx_offre_emploi_poste_id ON offre_emploi(poste_id)');
            $this->db->query(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'fk_offre_emploi_poste'
          AND conrelid = 'offre_emploi'::regclass
    ) THEN
        ALTER TABLE offre_emploi
            ADD CONSTRAINT fk_offre_emploi_poste
            FOREIGN KEY (poste_id) REFERENCES poste(id) ON DELETE RESTRICT;
    END IF;
END $$
SQL);
        }
    }

    public function down()
    {
        // Migration volontairement non destructive : aucune colonne n'est supprimée.
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
