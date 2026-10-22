<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$minBudget = old('budget_min', (string) ($request->budget_min ?? ''));
$maxBudget = old('budget_max', (string) ($request->budget_max ?? ''));
?>
<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 1 · ANALYSE RH</div>
        <h1>Examen RH — Demande D-<?= esc(str_pad((string) $request->id, 3, '0', STR_PAD_LEFT)) ?></h1>
        <p>Analysez la demande du manager et enregistrez la décision RH.</p>
    </div>
</div>

<section class="panel">
    <div class="panel-body">
        <p><strong>Demandeur :</strong> <?= esc($request->demandeur_prenom . ' ' . $request->demandeur_nom) ?></p>
        <p><strong>Département :</strong> <?= esc($request->nom_departement) ?></p>
        <p><strong>Intitulé :</strong> <?= esc($request->intitule) ?></p>
        <p><strong>Nombre de postes :</strong> <?= esc($request->nombre_postes) ?></p>
        <p><strong>Motif et justification :</strong><br><?= nl2br(esc($request->motif)) ?></p>
        <p><strong>Budget actuel :</strong> <?= esc($request->budget_min ?? '—') ?> - <?= esc($request->budget_max ?? '—') ?> Ar</p>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><h2 class="panel-title">Décision RH</h2></div>
    <div class="panel-body">
        <form method="post" action="<?= site_url('bloc1/rh-review/' . $request->id) ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field">
                    <label for="decision">Décision *</label>
                    <select name="decision" id="decision" required>
                        <option value="">-- Choisir --</option>
                        <option value="valider" <?= old('decision') === 'valider' ? 'selected' : '' ?>>Valider et transmettre à la Finance</option>
                        <option value="refuser" <?= old('decision') === 'refuser' ? 'selected' : '' ?>>Refuser</option>
                    </select>
                </div>
                <div class="field">
                    <label for="budget_min">Budget prévisionnel RH minimum *</label>
                    <input type="number" name="budget_min" id="budget_min" min="0" step="0.01" required value="<?= esc($minBudget, 'attr') ?>">
                </div>
                <div class="field">
                    <label for="budget_max">Budget prévisionnel RH maximum *</label>
                    <input type="number" name="budget_max" id="budget_max" min="0" step="0.01" required value="<?= esc($maxBudget, 'attr') ?>">
                </div>
                <div class="field full">
                    <label for="commentaire">Commentaire / justification *</label>
                    <textarea name="commentaire" id="commentaire" required><?= esc(old('commentaire')) ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Enregistrer la décision</button>
                <a class="btn btn-secondary" href="<?= site_url('bloc1/rh-review') ?>">Retour à la liste</a>
            </div>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
