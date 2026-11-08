<?php $hours = static fn (int $minutes): string => ($minutes < 0 ? '−' : '') . intdiv(abs($minutes), 60) . ' h ' . str_pad((string) (abs($minutes) % 60), 2, '0', STR_PAD_LEFT); ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><h1>Temps réellement travaillé</h1><p>Synthèse calculée depuis les plannings et pointages réels.</p></div></div>
<?php if ($calculationError): ?><div class="alert error"><?= esc($calculationError) ?></div><?php endif ?>
<section class="panel"><div class="panel-body"><form method="get" action="<?= site_url('bloc6/temps-travaille') ?>" class="toolbar">
    <div class="field"><label>Du</label><input type="date" name="date_debut" value="<?= esc($filters['date_debut'], 'attr') ?>" required></div><div class="field"><label>Au</label><input type="date" name="date_fin" value="<?= esc($filters['date_fin'], 'attr') ?>" required></div>
    <select name="employe_id"><option value="">Tous les salariés</option><?php foreach ($options['employes'] as $e): ?><option value="<?= (int) $e['id'] ?>" <?= (string) ($filters['employe_id'] ?? '') === (string) $e['id'] ? 'selected' : '' ?>><?= esc($e['matricule'] . ' · ' . $e['prenom'] . ' ' . $e['nom']) ?></option><?php endforeach ?></select>
    <select name="departement_id"><option value="">Tous les départements</option><?php foreach ($options['departements'] as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (string) ($filters['departement_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['nom_departement']) ?></option><?php endforeach ?></select>
    <button class="btn btn-primary">Calculer</button>
</form></div></section>
<section class="panel"><div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Salarié</th><th>Prévu</th><th>Travaillé net</th><th>Écart</th><th>Retards</th><th>Heures sup.</th><th>Incomplètes</th><th>Jours planifiés / pointés</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= esc($row['matricule'] . ' · ' . $row['prenom'] . ' ' . $row['nom']) ?><small><?= esc($row['departement'] ?? '') ?></small></td><td><?= esc($hours($row['planned_minutes'])) ?></td><td><?= esc($hours($row['worked_minutes'])) ?></td><td><?= esc($hours($row['difference_minutes'])) ?></td><td><?= (int) $row['delay_minutes'] ?> min</td><td><?= esc($hours($row['overtime_minutes'])) ?></td><td><?= (int) $row['incomplete_days'] ?></td><td><?= (int) $row['planned_days'] ?> / <?= (int) $row['punched_days'] ?></td></tr><?php endforeach ?>
<?php if (! $rows): ?><tr><td colspan="8">Aucun planning réel sur cette période.</td></tr><?php endif ?>
</tbody></table></div><p class="notice">Temps net = sortie − entrée − pause. Un pointage incomplet ne produit aucune durée. Les absences ne sont pas déduites tant que le contrat de validation de la partie C n’est pas confirmé.</p></div></section>
<?= $this->endSection() ?>
