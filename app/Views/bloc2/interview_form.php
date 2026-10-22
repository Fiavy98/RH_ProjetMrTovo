<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $old = session('old') ?? []; $v = fn ($key) => $old[$key] ?? $interview[$key] ?? ''; ?>
<div class="page-head">
    <div><div class="eyebrow">ENTRETIEN</div><h1><?= $interview ? 'Modifier la planification' : 'Planifier un entretien' ?></h1></div>
    <a class="btn btn-white" href="<?= site_url('bloc2/interviews') ?>">Retour</a>
</div>
<section class="panel">
    <div class="panel-head"><h2 class="panel-title">Planification</h2></div>
    <div class="panel-body">
        <form method="post" action="<?= site_url('bloc2/interviews/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc((string) ($interview['id'] ?? ''), 'attr') ?>">
            <div class="form-grid">
                <div class="field full">
                    <label>Candidature présélectionnée ou en entretien *</label>
                    <select name="candidature_id" required <?= $interview ? 'disabled' : '' ?>>
                        <option value="">Sélectionner</option>
                        <?php foreach ($applications as $application): ?>
                            <option value="<?= $application['id'] ?>" <?= ($v('candidature_id') ?: $selectedApplication) == $application['id'] ? 'selected' : '' ?>>
                                <?= esc($application['prenom'] . ' ' . $application['nom'] . ' · ' . $application['offre_titre'] . ' · ' . $application['statut']) ?>
                            </option>
                        <?php endforeach ?>
                        <?php if ($interview): ?>
                            <option value="<?= $interview['candidature_id'] ?>" selected><?= esc($interview['prenom'] . ' ' . $interview['nom'] . ' · ' . $interview['offre_titre']) ?></option>
                        <?php endif ?>
                    </select>
                    <?php if ($interview): ?><input type="hidden" name="candidature_id" value="<?= $interview['candidature_id'] ?>"><?php endif ?>
                </div>
                <div class="field"><label>Date et heure *</label><input type="datetime-local" name="date_entretien" required value="<?= esc(str_replace(' ', 'T', substr((string) $v('date_entretien'), 0, 16)), 'attr') ?>"></div>
                <div class="field"><label>Type *</label><select name="type_entretien" required><?php foreach (['RH', 'TECHNIQUE', 'FINAL', 'TEST_TECHNIQUE'] as $type): ?><option <?= $v('type_entretien') === $type ? 'selected' : '' ?>><?= $type ?></option><?php endforeach ?></select></div>
                <div class="field full"><label>Lieu *</label><input name="lieu" required value="<?= esc((string) $v('lieu'), 'attr') ?>"></div>
                <div class="field full">
                    <label>Participants / évaluateurs *</label>
                    <select name="participant_ids[]" multiple required size="5">
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= $employee['id'] ?>" <?= in_array((int) $employee['id'], array_map('intval', $selectedParticipants), true) ? 'selected' : '' ?>>
                                <?= esc($employee['prenom'] . ' ' . $employee['nom'] . ' · ' . $employee['matricule']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <small>Maintenez Ctrl pour sélectionner plusieurs participants.</small>
                </div>
            </div>
            <button class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</section>
<p class="muted small">Les participants sont affectés explicitement. Tant que l’authentification n’est pas reliée aux employés, RH saisit les avis au nom des participants affectés ; aucun manager n’obtient un accès global.</p>
<?= $this->endSection() ?>
