<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
/**
 * @var object|null $employee
 * @var object|null $bloc
 * @var array<int, object> $departments
 * @var array<int, object> $contracts
 * @var bool $formReady
 * @var string $formError
 */
$base = 'bloc' . $bloc;
?>

<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 1 · EXPRESSION DU BESOIN</div>
        <h1>Nouvelle demande</h1>
        <p>Créez une demande de recrutement en utilisant les données de référence de la base.</p>
    </div>
</div>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Nouvelle demande</h2>
            <p class="panel-sub">Le demandeur est affecté automatiquement. Les budgets sont facultatifs.</p>
        </div>
    </div>
    <div class="panel-body">
        <?php if (! empty($formError)): ?>
            <?= ui_notice($formError, 'danger') ?>
        <?php endif; ?>

        <form method="post" action="<?= site_url($base . '/demande-form') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field">
                    <label for="demandeur_id">Demandeur *</label>
                    <input
                        type="text"
                        id="demandeur_id"
                        value="<?= esc($employee === null ? '' : trim($employee->prenom . ' ' . $employee->nom . ' (' . $employee->matricule . ')')) ?>"
                        readonly
                    >
                    <small>Employé actif sélectionné automatiquement.</small>
                </div>

                <div class="field">
                    <label for="departement_id">Département *</label>
                    <select name="departement_id" id="departement_id" required>
                        <option value="">-- Sélectionner un département --</option>
                        <?php foreach ($departments as $department): ?>
                            <option
                                value="<?= esc((string) $department->id, 'attr') ?>"
                                <?= (string) old('departement_id') === (string) $department->id ? 'selected' : '' ?>
                            >
                                <?= esc($department->nom_departement) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="type_contrat_id">Type de contrat *</label>
                    <select name="type_contrat_id" id="type_contrat_id" required>
                        <option value="">-- Sélectionner un type de contrat --</option>
                        <?php foreach ($contracts as $contract): ?>
                            <option value="<?= esc((string) $contract->id, 'attr') ?>" <?= (string) old('type_contrat_id') === (string) $contract->id ? 'selected' : '' ?>>
                                <?= esc($contract->libelle) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field full">
                    <label for="motif">Motif et justification *</label>
                    <textarea name="motif" id="motif" required><?= esc(old('motif')) ?></textarea>
                    <small>Expliquez le besoin et justifiez la demande.</small>
                </div>

                <div class="field">
                    <label for="intitule">Intitulé du poste *</label>
                    <input type="text" name="intitule" id="intitule" maxlength="150" required value="<?= esc(old('intitule'), 'attr') ?>">
                </div>

                <div class="field">
                    <label for="nombre_postes">Nombre de postes *</label>
                    <input type="number" name="nombre_postes" id="nombre_postes" min="1" step="1" required value="<?= esc(old('nombre_postes', '1'), 'attr') ?>">
                    <small>Les postes seront différenciés par un suffixe (_1, _2, etc.).</small>
                </div>

                <div class="field">
                    <label for="budget_min">Budget prévisionnel minimum (Ar)</label>
                    <input type="number" name="budget_min" id="budget_min" min="0" step="0.01" value="<?= esc(old('budget_min'), 'attr') ?>">
                    <small>Facultatif.</small>
                </div>

                <div class="field">
                    <label for="budget_max">Budget prévisionnel maximum (Ar)</label>
                    <input type="number" name="budget_max" id="budget_max" min="0" step="0.01" value="<?= esc(old('budget_max'), 'attr') ?>">
                    <small>Facultatif.</small>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit" <?= empty($formReady) ? 'disabled' : '' ?>>
                    Enregistrer la demande
                </button>
                <a class="btn btn-secondary" href="<?= site_url($base . '/requests') ?>">Annuler</a>
            </div>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
