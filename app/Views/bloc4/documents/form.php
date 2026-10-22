<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · DOCUMENTS</div><h1>Ajouter un document</h1><p>Les métadonnées seront rattachées à un salarié et facultativement à un de ses contrats.</p></div><div class="action-row"><a class="btn btn-secondary" href="<?= site_url('bloc4/documents') ?>">← Documents</a></div></div>
<?= ui_notice('Formats admis : PDF, JPEG, PNG, WebP et texte. Le fichier est enregistré hors du dossier public.') ?>
<form method="post" enctype="multipart/form-data" action="<?= site_url('bloc4/document-form') ?>"><?= csrf_field() ?><div class="panel"><div class="panel-head"><div><h2 class="panel-title">Informations du fichier</h2></div></div><div class="panel-body"><div class="form-grid">
<?php $employees = []; foreach ($options['employes'] as $employee) $employees[$employee['id']] = $employee['matricule'] . ' · ' . $employee['prenom'] . ' ' . $employee['nom']; ?>
<?= ui_field('employe_id', 'Salarié', 'select', '', ['required' => true, 'options' => $employees]) ?>
<?= ui_field('type_document', 'Type de document', 'select', 'autre', ['required' => true, 'options' => ['contrat_signe' => 'Contrat signé', 'piece_identite' => 'Pièce d’identité', 'autre' => 'Autre']]) ?>
<?php $contracts = ['' => 'Aucun contrat associé']; foreach ($options['contrats'] as $contract) $contracts[$contract['id']] = '#' . $contract['id'] . ' · ' . $contract['code'] . ' · ' . ui_date($contract['date_debut']); ?>
<?= ui_field('contrat_id', 'Contrat (facultatif)', 'select', '', ['options' => $contracts]) ?>
<div class="field"><label for="f-fichier">Fichier *</label><input type="file" name="fichier" id="f-fichier" accept=".pdf,.jpg,.jpeg,.png,.webp,.txt" required></div>
</div><div class="form-actions"><button class="btn btn-primary" type="submit">Enregistrer le document</button><a class="btn btn-secondary" href="<?= site_url('bloc4/documents') ?>">Annuler</a></div></div></div></form>
<?= $this->endSection() ?>
