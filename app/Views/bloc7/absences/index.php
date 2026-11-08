<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><h1>Absences et justificatifs</h1><p>Registre réel des absences, pièces et statuts.</p></div><a class="btn btn-primary" href="<?= site_url('bloc7/absences/nouveau') ?>">Nouvelle absence</a></div>
<section class="panel"><div class="panel-body"><form method="get" action="<?= site_url('bloc7/absences') ?>" class="toolbar">
<input type="date" name="date_debut" value="<?= esc($filters['date_debut'] ?? '', 'attr') ?>"><input type="date" name="date_fin" value="<?= esc($filters['date_fin'] ?? '', 'attr') ?>">
<select name="employe_id"><option value="">Tous les salariés</option><?php foreach ($options['employes'] as $e): ?><option value="<?= (int) $e['id'] ?>" <?= (string) ($filters['employe_id'] ?? '') === (string) $e['id'] ? 'selected' : '' ?>><?= esc($e['matricule'] . ' · ' . $e['prenom'] . ' ' . $e['nom']) ?></option><?php endforeach ?></select>
<select name="departement_id"><option value="">Tous les départements</option><?php foreach ($options['departements'] as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (string) ($filters['departement_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['nom_departement']) ?></option><?php endforeach ?></select>
<select name="statut"><option value="">Tous les statuts</option><?php foreach (['ENREGISTREE','A_VERIFIER','JUSTIFIEE','NON_JUSTIFIEE','ANNULEE'] as $v): ?><option <?= ($filters['statut'] ?? '') === $v ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach ?></select><button class="btn btn-secondary">Filtrer</button>
</form></div></section>
<section class="panel"><div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Salarié</th><th>Période</th><th>Type / motif</th><th>Justificatif</th><th>Statut</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= esc($row['matricule'] . ' · ' . $row['prenom'] . ' ' . $row['nom']) ?><small><?= esc($row['nom_departement'] ?? '') ?></small></td><td><?= esc($row['date_debut'] . ' → ' . $row['date_fin']) ?></td><td><?= esc($row['type_libelle'] ?? $row['motif']) ?><small><?= esc($row['motif']) ?></small></td><td><?= $row['justificatif_nom_stocke'] ? 'Oui' : 'Non' ?></td><td><?= esc($row['statut']) ?></td><td><a class="btn btn-secondary" href="<?= site_url('bloc7/absences/' . $row['id']) ?>">Consulter</a></td></tr><?php endforeach ?>
<?php if (! $rows): ?><tr><td colspan="6">Aucune absence trouvée.</td></tr><?php endif ?>
</tbody></table></div></div></section>
<?= $this->endSection() ?>
