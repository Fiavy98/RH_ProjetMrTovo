<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <div class="eyebrow"><?= esc($blockTitle) ?></div>
        <h1><?= esc($screen['title']) ?></h1>
        <p><?= esc($screen['subtitle']) ?> · Maquette statique</p>
    </div>
</div>

<?= ui_notice('Écran de cadrage avec données fictives. Les formulaires et actions ne sont pas connectés à une logique métier.') ?>

<?php if (! empty($screen['stats'])): ?>
<div class="cards">
    <?php foreach ($screen['stats'] as $stat): ?>
    <div class="stat"><div class="stat-ico">◈</div><div class="num"><?= esc($stat[1]) ?></div><div class="label"><?= esc($stat[0]) ?></div></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (! empty($screen['fields'])): ?>
<section class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Saisie / informations principales</h2><p class="panel-sub">Présentation des champs prévus pour cette fonctionnalité.</p></div></div>
    <div class="panel-body">
        <div class="form-grid">
            <?php foreach ($screen['fields'] as $field): ?>
            <div class="field"><label><?= esc($field[0]) ?></label><input type="text" placeholder="À compléter" disabled><small><?= esc($field[1]) ?></small></div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="button" disabled>Enregistrer — à développer</button></div>
    </div>
</section>
<?php endif; ?>

<?php if (! empty($screen['columns'])): ?>
<?php
$rows = [];
foreach ($screen['rows'] ?? [] as $row) {
    $rows[] = array_map(static fn ($value) => (string) $value, $row);
}
?>
<?= ui_panel($screen['title'], ui_table($screen['columns'], $rows, 'Aucune donnée de démonstration.')) ?>
<?php endif; ?>

<?php if (! empty($screen['notice'])): ?>
<?= ui_notice($screen['notice'], 'warn') ?>
<?php endif; ?>

<section class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Périmètre de la fonctionnalité</h2></div></div>
    <div class="panel-body"><p><?= esc($screen['description']) ?></p></div>
</section>
<?= $this->endSection() ?>
