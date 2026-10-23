<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $old = session('old') ?? []; $v = static fn (string $key) => $old[$key] ?? $offer[$key] ?? ''; ?>
<div class="page-head">
    <div>
        <div class="eyebrow">OFFRE</div>
        <h1><?= $offer ? 'Fiche et modification' : 'Créer une offre' ?></h1>
        <p>Chaque offre concerne un poste précis issu d'une demande validée par la Direction.</p>
    </div>
    <a class="btn btn-white" href="<?= site_url('bloc2/offers') ?>">Retour</a>
</div>
<?php ob_start(); ?>
<form method="post" action="<?= site_url('bloc2/offers/save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= esc((string) ($offer['id'] ?? ''), 'attr') ?>">
    <div class="form-grid">
        <div class="field full">
            <label>Poste à recruter *</label>
            <select name="poste_id" id="poste_id" required <?= ($offer && $offer['statut'] !== 'BROUILLON') ? 'disabled' : '' ?>>
                <option value="">Sélectionner un poste</option>
                <?php foreach ($positions as $position): ?>
                    <option value="<?= (int) $position['id'] ?>"
                            data-demande-id="<?= (int) $position['demande_id'] ?>"
                            <?= (string) $v('poste_id') === (string) $position['id'] ? 'selected' : '' ?>>
                        <?= esc($position['intitule'] . ' · demande #' . $position['demande_id']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="field full">
            <label>Demande approuvée *</label>
            <select name="demande_recrutement_id" id="demande_recrutement_id" required <?= ($offer && $offer['statut'] !== 'BROUILLON') ? 'disabled' : '' ?>>
                <option value="">Sélectionner</option>
                <?php foreach ($requests as $request): ?>
                    <option value="<?= (int) $request['id'] ?>" <?= (string) $v('demande_recrutement_id') === (string) $request['id'] ? 'selected' : '' ?>>
                        <?= esc(($request['intitule'] ?? 'Demande') . ' · #' . $request['id']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="field full"><label>Titre *</label><input name="titre" maxlength="150" required value="<?= esc((string) $v('titre'), 'attr') ?>"></div>
        <div class="field full"><label>Description</label><textarea name="description"><?= esc((string) $v('description')) ?></textarea></div>
        <div class="field"><label>Date de clôture</label><input type="date" name="date_cloture" value="<?= esc(substr((string) $v('date_cloture'), 0, 10), 'attr') ?>"></div>
        <div class="field"><label>Statut</label><input readonly value="<?= esc((string) ($offer['statut'] ?? 'BROUILLON'), 'attr') ?>"></div>
    </div>
    <div class="action-row"><button class="btn btn-primary" <?= ($offer && $offer['statut'] !== 'BROUILLON') ? 'disabled' : '' ?>>Enregistrer le brouillon</button></div>
</form>
<script>
document.getElementById('poste_id')?.addEventListener('change', function () {
    const requestId = this.options[this.selectedIndex]?.dataset.demandeId;
    if (requestId) document.getElementById('demande_recrutement_id').value = requestId;
});
</script>
<?php $form = ob_get_clean(); ?>
<?= ui_panel('Informations de l’offre', $form) ?>
<?= $this->endSection() ?>
