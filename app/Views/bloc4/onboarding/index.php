<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · ONBOARDING</div><h1>Liste des onboardings</h1><p>Suivez l'avancement des six tâches d'intégration de chaque nouvel employé.</p></div></div>
<form class="toolbar" method="get"><select name="statut"><option value="">Tous les statuts</option><?php foreach (['a_faire', 'en_cours', 'termine'] as $status): ?><option value="<?= esc($status, 'attr') ?>" <?= $filter === $status ? 'selected' : '' ?>><?= esc(ui_status_label($status)) ?></option><?php endforeach; ?></select><button type="submit" class="btn btn-secondary">Filtrer</button></form>
<?php $rows = []; foreach ($onboardings as $item) { $total = (int) $item['total_taches']; $done = (int) $item['taches_terminees']; $rows[] = [esc($item['matricule']), esc($item['prenom'] . ' ' . $item['nom']), $done . ' / ' . $total, ui_raw(ui_badge($item['statut'])), ui_date($item['cree_le']), ui_raw('<a class="btn btn-secondary" href="' . site_url('bloc4/onboarding-detail/' . $item['id']) . '">Suivre les tâches</a>')]; } ?>
<?= ui_panel('Salariés en intégration', ui_table(['Matricule', 'Salarié', 'Avancement', 'Statut', 'Créé le', 'Action'], $rows, 'Aucun onboarding n’a été créé. Un onboarding est initialisé à la signature du premier contrat.')) ?>
<?= $this->endSection() ?>
