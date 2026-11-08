<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><h1>Anomalies de pointage</h1><p>Détection et traitement des écarts issus du planning et des pointages réels.</p></div></div>
<div class="stats-grid">
    <?php foreach (['A_EXAMINER' => 'À examiner', 'EN_COURS' => 'En cours', 'REGULARISEE' => 'Régularisées', 'IGNOREE' => 'Ignorées'] as $key => $label): ?>
        <div class="stat-card"><span><?= esc($label) ?></span><strong><?= (int) ($stats[$key] ?? 0) ?></strong></div>
    <?php endforeach ?>
</div>
<section class="panel"><div class="panel-head"><h2 class="panel-title">Détecter sur une période</h2></div><div class="panel-body">
    <form method="post" action="<?= site_url('bloc6/anomalies/detecter') ?>" class="toolbar"><?= csrf_field() ?>
        <div class="field"><label>Du</label><input type="date" name="date_debut" value="<?= esc($filters['date_debut'], 'attr') ?>" required></div>
        <div class="field"><label>Au</label><input type="date" name="date_fin" value="<?= esc($filters['date_fin'], 'attr') ?>" required></div>
        <button class="btn btn-primary" type="submit">Lancer la détection</button>
    </form>
</div></section>
<section class="panel"><div class="panel-head"><h2 class="panel-title">Anomalies détectées</h2></div><div class="panel-body">
    <form method="get" action="<?= site_url('bloc6/anomalies') ?>" class="toolbar">
        <input type="date" name="date_debut" value="<?= esc($filters['date_debut'] ?? '', 'attr') ?>">
        <input type="date" name="date_fin" value="<?= esc($filters['date_fin'] ?? '', 'attr') ?>">
        <select name="employe_id"><option value="">Tous les salariés</option><?php foreach ($options['employes'] as $e): ?><option value="<?= (int) $e['id'] ?>" <?= (string) ($filters['employe_id'] ?? '') === (string) $e['id'] ? 'selected' : '' ?>><?= esc($e['matricule'] . ' · ' . $e['prenom'] . ' ' . $e['nom']) ?></option><?php endforeach ?></select>
        <select name="departement_id"><option value="">Tous les départements</option><?php foreach ($options['departements'] as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (string) ($filters['departement_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['nom_departement']) ?></option><?php endforeach ?></select>
        <select name="type"><option value="">Tous les types</option><?php foreach (['ENTREE_SANS_SORTIE','SORTIE_SANS_ENTREE','JOURNEE_SANS_POINTAGE','RETARD','HEURES_INCOHERENTES','ECART_A_VERIFIER'] as $v): ?><option <?= ($filters['type'] ?? '') === $v ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach ?></select>
        <select name="statut"><option value="">Tous les statuts</option><?php foreach (['A_EXAMINER','EN_COURS','REGULARISEE','IGNOREE'] as $v): ?><option <?= ($filters['statut'] ?? '') === $v ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach ?></select>
        <button class="btn btn-secondary">Filtrer</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Salarié</th><th>Date</th><th>Type</th><th>Détail</th><th>Statut</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $row): ?><tr><td><?= esc($row['matricule'] . ' · ' . $row['prenom'] . ' ' . $row['nom']) ?><small><?= esc($row['nom_departement'] ?? '') ?></small></td><td><?= esc($row['date_anomalie']) ?></td><td><?= esc(str_replace('_', ' ', $row['type'])) ?></td><td><?= esc($row['detail']) ?></td><td><?= esc($row['statut']) ?></td><td><a class="btn btn-secondary" href="<?= site_url('bloc6/anomalies/' . $row['id']) ?>">Traiter</a></td></tr><?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="6">Aucune anomalie pour ces filtres.</td></tr><?php endif ?>
    </tbody></table></div>
</div></section>
<?= $this->endSection() ?>
