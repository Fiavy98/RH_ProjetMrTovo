<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$employee = $employee ?? null;
$search = trim((string) ($search ?? ''));
$searchResults = $searchResults ?? [];
$contracts = $contracts ?? [];
$affectations = $affectations ?? [];
$salaryHistory = $salaryHistory ?? [];
$dateDebut = (string) ($dateDebut ?? '');
$dateFin = (string) ($dateFin ?? '');
$message = $message ?? session()->getFlashdata('message');
$error = $error ?? session()->getFlashdata('error');

$formatDate = static function (?string $value): string {
    if ($value === null || $value === '') {
        return '—';
    }

    $date = strtotime((string) $value);

    return $date === false ? (string) $value : date('d/m/Y', $date);
};

$formatMoney = static function ($value): string {
    if ($value === null || $value === '') {
        return '—';
    }

    $amount = (float) $value;

    return number_format($amount, 0, ',', ' ') . ' Ar';
};
?>
<div class="page-head">
    <div>
        <div class="eyebrow"><?= esc($blockTitle) ?></div>
        <h1>Carrière et rémunération</h1>
        <?php if ($employee !== null): ?>
            <p><?= esc($employee['nom_complet'] ?? '') ?> · Matricule : <?= esc($employee['matricule'] ?? '—') ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php if ($employee !== null): ?>
            <a class="btn btn-white" href="<?= site_url('bloc5/dossier/' . (int) ($employee['id'] ?? 0)) ?>">Retour au dossier</a>
            <a class="btn btn-secondary" href="<?= site_url('bloc5/carriere') ?>">Choisir un autre salarié</a>
        <?php else: ?>
            <a class="btn btn-white" href="<?= site_url('bloc5/dossiers') ?>">Retour au répertoire</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($message): ?>
    <?= ui_notice((string) $message, 'success') ?>
<?php endif; ?>
<?php if ($error): ?>
    <?= ui_notice((string) $error, 'error') ?>
<?php endif; ?>

<?php if ($employee === null): ?>
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Choisir un salarié</h2>
                <p class="panel-sub">Rechercher par matricule, nom ou prénom.</p>
            </div>
        </div>
        <div class="panel-body">
            <form method="get" action="<?= site_url('bloc5/carriere') ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
                <div class="field" style="min-width:320px;">
                    <label for="career-search">Salarié</label>
                    <input id="career-search" name="q" type="search" value="<?= esc($search, 'attr') ?>" placeholder="Ex. EMP-0011 ou Rakoto" autocomplete="off">
                </div>
                <button class="btn btn-secondary" type="submit">Rechercher</button>
            </form>
        </div>
    </section>

    <?php if ($search !== ''): ?>
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Résultats</h2>
                </div>
            </div>
            <div class="panel-body">
                <?php if (empty($searchResults)): ?>
                    <p>Aucun salarié ne correspond à votre recherche.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Matricule</th>
                                <th>Nom et prénom</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $result): ?>
                                <tr>
                                    <td><?= esc((string) ($result['matricule'] ?? '—')) ?></td>
                                    <td><?= esc(trim((string) ($result['prenom'] ?? '') . ' ' . (string) ($result['nom'] ?? ''))) ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="<?= site_url('bloc5/carriere/' . (int) ($result['id'] ?? 0)) ?>">Sélectionner</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>

<?php if ($employee !== null): ?>
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Filtrer par période</h2>
            </div>
        </div>
        <div class="panel-body">
            <form method="get" action="<?= site_url('bloc5/carriere/' . (int) ($employee['id'] ?? 0)) ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
                <div class="field" style="min-width:220px;">
                    <label for="date_debut">Date de début</label>
                    <input id="date_debut" name="date_debut" type="date" value="<?= esc($dateDebut, 'attr') ?>">
                </div>
                <div class="field" style="min-width:220px;">
                    <label for="date_fin">Date de fin</label>
                    <input id="date_fin" name="date_fin" type="date" value="<?= esc($dateFin, 'attr') ?>">
                </div>
                <button class="btn btn-secondary" type="submit">Appliquer</button>
                <?php if ($dateDebut !== '' || $dateFin !== ''): ?>
                    <a class="btn btn-white" href="<?= site_url('bloc5/carriere/' . (int) ($employee['id'] ?? 0)) ?>">Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Historique des contrats</h2>
            </div>
        </div>
        <div class="panel-body">
            <?php if (empty($contracts)): ?>
                <p>Aucun contrat enregistré pour ce salarié.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Signature</th>
                            <th>Salaire</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $contract): ?>
                            <tr>
                                <td><?= esc((string) ($contract['type_code'] ?? '—')) ?></td>
                                <td><?= esc($formatDate((string) ($contract['date_debut'] ?? ''))) ?></td>
                                <td><?= $contract['date_fin'] === null || $contract['date_fin'] === '' ? 'En cours' : esc($formatDate((string) $contract['date_fin'])) ?></td>
                                <td><?= esc($formatDate((string) ($contract['date_signature'] ?? ''))) ?></td>
                                <td><?= esc($formatMoney((float) ($contract['salaire'] ?? 0))) ?></td>
                                <td><?= esc((string) ($contract['statut'] ?? '—')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Historique des affectations</h2>
            </div>
        </div>
        <div class="panel-body">
            <?php if (empty($affectations)): ?>
                <p>Aucune affectation enregistrée pour ce salarié.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Département</th>
                            <th>Poste</th>
                            <th>Début</th>
                            <th>Fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($affectations as $affectation): ?>
                            <tr>
                                <td><?= esc((string) ($affectation['departement'] ?? '—')) ?></td>
                                <td><?= esc((string) ($affectation['poste'] ?? '—')) ?></td>
                                <td><?= esc($formatDate((string) ($affectation['date_debut'] ?? ''))) ?></td>
                                <td><?= $affectation['date_fin'] === null || $affectation['date_fin'] === '' ? 'En cours' : esc($formatDate((string) $affectation['date_fin'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Historique des rémunérations</h2>
            </div>
        </div>
        <div class="panel-body">
            <?php if (empty($salaryHistory)): ?>
                <p>Aucun historique de rémunération disponible.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ancien salaire</th>
                            <th>Nouveau salaire</th>
                            <th>Date d’effet</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salaryHistory as $salary): ?>
                            <tr>
                                <td><?= esc($formatMoney((float) ($salary['ancien_salaire'] ?? 0))) ?></td>
                                <td><?= esc($formatMoney((float) ($salary['nouveau_salaire'] ?? 0))) ?></td>
                                <td><?= esc($formatDate((string) ($salary['date_effet'] ?? ''))) ?></td>
                                <td><?= esc((string) ($salary['motif'] ?? '—')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?= $this->endSection() ?>
