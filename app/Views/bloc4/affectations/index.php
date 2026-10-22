<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · AFFECTATIONS</div><h1>Gestion des affectations</h1><p>Écran provisoire de démonstration, en attendant l'intégration de la fonctionnalité du Bloc 3.</p></div></div>
<?= ui_notice('Un employé ne peut avoir qu’un poste actif et un poste ne peut avoir qu’un titulaire actif. Cette écriture est temporaire et devra être raccordée au Bloc 3.') ?>
<div class="grid2"><form method="post" action="<?= site_url('bloc4/assignment-b4') ?>"><?= csrf_field() ?><div class="panel"><div class="panel-head"><div><h2 class="panel-title">Nouvelle affectation de simulation</h2></div></div><div class="panel-body"><div class="form-grid">
<?php $employeeOptions = []; foreach ($employesLibres as $employee) $employeeOptions[$employee['id']] = $employee['matricule'] . ' · ' . $employee['prenom'] . ' ' . $employee['nom']; ?>
<?= ui_field('employe_id', 'Salarié', 'select', '', ['required' => true, 'options' => $employeeOptions]) ?>
<?php $positionOptions = []; foreach ($postesLibres as $position) $positionOptions[$position['id']] = $position['intitule'] . ' · ' . $position['nom_departement']; ?>
<?= ui_field('poste_id', 'Poste', 'select', '', ['required' => true, 'options' => $positionOptions]) ?>
<?= ui_field('date_debut', 'Date de début', 'date', date('Y-m-d'), ['required' => true]) ?>
</div><div class="form-actions"><button class="btn btn-primary" type="submit">Enregistrer l’affectation</button></div></div></div></form>
<?php $rows = []; foreach ($affectations as $assignment) $rows[] = [esc($assignment['matricule']), esc($assignment['prenom'] . ' ' . $assignment['nom']), esc($assignment['intitule']), ui_date($assignment['date_debut']), ui_date($assignment['date_fin']), ui_raw(ui_badge($assignment['date_fin'] === null ? 'en_cours' : 'termine'))]; ?>
<?= ui_panel('Historique des affectations', ui_table(['Matricule', 'Salarié', 'Poste', 'Début', 'Fin', 'Statut'], $rows, 'Aucune affectation enregistrée.')) ?>
</div>
<?= $this->endSection() ?>
