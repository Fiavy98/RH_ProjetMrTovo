<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · CONTRATS</div><h1>Renouveler le contrat #<?= esc($contrat['id']) ?></h1><p>Le renouvellement constitue une nouvelle période contractuelle liée au contrat précédent.</p></div><div class="action-row"><a class="btn btn-secondary" href="<?= site_url('bloc4/contract-detail/' . $contrat['id']) ?>">← Fiche contrat</a></div></div>
<?= ui_notice('Un seul renouvellement est autorisé. La nouvelle période commence obligatoirement le lendemain de la date de fin du contrat précédent.') ?>
<form method="post" action="<?= site_url('bloc4/renewals/' . $contrat['id']) ?>"><?= csrf_field() ?><div class="panel"><div class="panel-head"><div><h2 class="panel-title">Nouvelle période</h2></div></div><div class="panel-body"><div class="form-grid">
    <?= ui_field('previous', 'Contrat précédent', 'text', '#' . $contrat['id'], ['readonly' => true]) ?>
    <?= ui_field('type', 'Type conservé', 'text', $contrat['type_code'] . ' · ' . $contrat['type_libelle'], ['readonly' => true]) ?>
    <?= ui_field('date_debut', 'Date de début (lendemain de la fin précédente)', 'date', $dateDebut, ['required' => true, 'readonly' => true]) ?>
    <?= ui_field('date_fin', 'Dernier jour couvert', 'date', '', ['required' => true]) ?>
    <?= ui_field('salaire', 'Salaire (Ar)', 'number', $contrat['salaire'], ['required' => true, 'min' => 0, 'step' => '0.01']) ?>
    <?= ui_field('date_signature', 'Date de signature', 'date', '') ?>
</div><div class="form-actions"><button class="btn btn-primary" type="submit">Créer le renouvellement</button><a class="btn btn-secondary" href="<?= site_url('bloc4/contract-detail/' . $contrat['id']) ?>">Annuler</a></div></div></div></form>
<?= $this->endSection() ?>
