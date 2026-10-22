<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = $contrat !== null; $formAction = $isEdit ? 'bloc4/contract-form/' . $contrat['id'] : 'bloc4/contract-form'; ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · CONTRATS</div><h1><?= esc($pageTitle) ?></h1><p>Le nouveau contrat est enregistré au statut brouillon. Les durées sont vérifiées en mois calendaires.</p></div><div class="action-row"><a class="btn btn-secondary" href="<?= site_url('bloc4/contracts') ?>">← Liste des contrats</a></div></div>
<?= ui_notice('La date de fin est le dernier jour couvert. La durée maximale est respectée si le lendemain de cette date ne dépasse pas la date anniversaire calculée en mois calendaires.') ?>
<form method="post" action="<?= site_url($formAction) ?>">
    <?= csrf_field() ?>
    <div class="panel"><div class="panel-head"><div><h2 class="panel-title">Données du contrat</h2><p class="panel-sub">CE : 6 mois par période · CDD : 12 mois par période · un renouvellement maximum.</p></div></div><div class="panel-body"><div class="form-grid">
        <?php $employeeOptions = []; foreach ($options['employes'] as $employee) $employeeOptions[$employee['id']] = $employee['matricule'] . ' · ' . $employee['prenom'] . ' ' . $employee['nom']; ?>
        <?= ui_field('employe_id', 'Salarié', 'select', $contrat['employe_id'] ?? '', ['required' => true, 'options' => $employeeOptions]) ?>
        <?php $selectedType = old('type_contrat_id', $contrat['type_contrat_id'] ?? ''); ?>
        <div class="field"><label for="type_contrat_id">Type de contrat *</label><select name="type_contrat_id" id="type_contrat_id" data-contract-type required><?php foreach ($options['types'] as $type): ?><option value="<?= esc($type['id'], 'attr') ?>" data-code="<?= esc($type['code'], 'attr') ?>" <?= (string) $selectedType === (string) $type['id'] ? 'selected' : '' ?>><?= esc($type['code'] . ' · ' . $type['libelle']) ?></option><?php endforeach; ?></select></div>
        <?= ui_field('date_debut', 'Date de début', 'date', isset($contrat['date_debut']) ? date('Y-m-d', strtotime($contrat['date_debut'])) : '', ['required' => true]) ?>
        <div class="field"><label for="f-date_fin">Date de fin (obligatoire sauf CDI)</label><input type="date" name="date_fin" id="f-date_fin" value="<?= esc(old('date_fin', isset($contrat['date_fin']) ? date('Y-m-d', strtotime($contrat['date_fin'])) : ''), 'attr') ?>"></div>
        <?= ui_field('date_signature', 'Date de signature (facultative au brouillon)', 'date', isset($contrat['date_signature']) ? date('Y-m-d', strtotime($contrat['date_signature'])) : '') ?>
        <?= ui_field('salaire', 'Salaire (Ar)', 'number', $contrat['salaire'] ?? '', ['required' => true, 'min' => 0, 'step' => '0.01']) ?>
        <?php if ($isEdit): ?><div class="field"><label>Statut</label><input value="<?= esc(ui_status_label($contrat['statut'])) ?>" readonly></div><?php endif; ?>
    </div><div class="form-actions"><button class="btn btn-primary" type="submit">Enregistrer le brouillon</button><a class="btn btn-secondary" href="<?= site_url($isEdit ? 'bloc4/contract-detail/' . $contrat['id'] : 'bloc4/contracts') ?>">Annuler</a></div></div></div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.querySelector('[data-contract-type]');
    const end = document.getElementById('f-date_fin');
    if (!type || !end) return;
    const syncEndDate = function () {
        const isCdi = type.selectedOptions[0]?.dataset.code === 'CDI';
        end.disabled = isCdi;
        end.required = !isCdi;
        if (isCdi) end.value = '';
    };
    type.addEventListener('change', syncEndDate);
    syncEndDate();
});
</script>
<?= $this->endSection() ?>
