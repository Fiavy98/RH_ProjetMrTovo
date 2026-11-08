<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><p class="eyebrow">Congés et absences</p><h1><?= esc($pageTitle) ?></h1><p>Demandes saisies et suivies par les équipes autorisées.</p></div>
<?php if (($action ?? '') === 'demandes'): ?><a class="btn primary" href="<?= site_url('bloc7/demandes/nouvelle') ?>">＋ Nouvelle demande</a><?php endif; ?></div>
<?php if (isset($mode) && $mode==='absence'):
$rows=array_map(static fn($r)=>[$r['matricule'].' · '.$r['prenom'].' '.$r['nom'],$r['libelle'],ui_date($r['date_debut']).' – '.ui_date($r['date_fin']),$r['duree'].' '.$r['unite_decompte'],ui_status_label($r['statut']),$r['demande_conge_id']],$rows); endif; ?>
<?= ui_panel($pageTitle, ui_table($columns,$rows), 'Les demandes à corriger peuvent être modifiées par leur créateur puis soumises à nouveau.') ?>
<?= $this->endSection() ?>
