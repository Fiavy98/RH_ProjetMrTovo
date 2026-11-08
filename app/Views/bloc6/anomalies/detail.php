<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><h1>Traiter l’anomalie #<?= (int) $anomalie['id'] ?></h1><p><?= esc($anomalie['matricule'] . ' · ' . $anomalie['prenom'] . ' ' . $anomalie['nom']) ?></p></div><a class="btn btn-white" href="<?= site_url('bloc6/anomalies') ?>">Retour</a></div>
<div class="grid2"><section class="panel"><div class="panel-head"><h2 class="panel-title">Constat</h2></div><div class="panel-body">
    <dl><dt>Date</dt><dd><?= esc($anomalie['date_anomalie']) ?></dd><dt>Type</dt><dd><?= esc(str_replace('_', ' ', $anomalie['type'])) ?></dd><dt>Détail</dt><dd><?= esc($anomalie['detail']) ?></dd><dt>Statut</dt><dd><?= esc($anomalie['statut']) ?></dd><dt>Dernière détection</dt><dd><?= esc($anomalie['derniere_detection_le']) ?></dd></dl>
    <p class="notice">Le traitement ci-dessous ne modifie jamais le pointage. Toute correction doit être faite explicitement dans l’écran Pointages de B avec son motif.</p>
</div></section><section class="panel"><div class="panel-head"><h2 class="panel-title">Décision RH</h2></div><div class="panel-body">
    <form method="post" action="<?= site_url('bloc6/anomalies/' . $anomalie['id'] . '/traiter') ?>"><?= csrf_field() ?>
        <div class="field"><label>Statut</label><select name="statut" required><?php foreach (['A_EXAMINER','EN_COURS','REGULARISEE','IGNOREE'] as $v): ?><option <?= $anomalie['statut'] === $v ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach ?></select></div>
        <div class="field"><label>Motif (obligatoire pour régulariser ou ignorer)</label><textarea name="motif"><?= esc(old('motif', $anomalie['motif_traitement'] ?? '')) ?></textarea></div>
        <button class="btn btn-primary">Enregistrer la décision</button>
    </form>
</div></section></div>
<section class="panel"><div class="panel-head"><h2 class="panel-title">Historique</h2></div><div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Date</th><th>Auteur</th><th>Changement</th><th>Motif</th></tr></thead><tbody>
<?php foreach ($anomalie['historique'] as $h): ?><tr><td><?= esc($h['cree_le']) ?></td><td><?= esc(trim(($h['prenom'] ?? '') . ' ' . ($h['nom'] ?? ''))) ?></td><td><?= esc(($h['ancien_statut'] ?? '—') . ' → ' . $h['nouveau_statut']) ?></td><td><?= esc($h['motif'] ?? '—') ?></td></tr><?php endforeach ?>
<?php if (! $anomalie['historique']): ?><tr><td colspan="4">Aucun traitement enregistré.</td></tr><?php endif ?>
</tbody></table></div></div></section>
<?= $this->endSection() ?>
