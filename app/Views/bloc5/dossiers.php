<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$pdfAvailable = class_exists('Dompdf\\Dompdf') || class_exists('Mpdf\\Mpdf') || class_exists('TCPDF');
$search = (string) ($filters['q'] ?? '');
$departmentFilter = (string) ($filters['departement'] ?? '');
$statusFilter = (string) ($filters['statut'] ?? '');
$pagination = $pagination ?? ['currentPage' => 1, 'totalPages' => 1, 'pages' => [1], 'hasPrev' => false, 'hasNext' => false, 'start' => 0, 'end' => 0];
$rows = [];
foreach ($employees as $employee):
    $rows[] = [
        esc($employee['matricule'] ?? '—'),
        esc($employee['nom'] ?? '') . ' ' . esc($employee['prenom'] ?? ''),
        esc($employee['departement'] ?? '—'),
        esc($employee['poste_actuel'] ?? '—'),
        esc($employee['date_embauche'] ?? '—'),
        esc($employee['statut'] ?? 'Inactif'),
        ui_raw('<a class="btn btn-secondary" href="' . esc(site_url('bloc5/dossier/' . (int) ($employee['id'] ?? 0)), 'attr') . '">Ouvrir</a>'),
    ];
endforeach;
?>

<div class="page-head">
    <div>
        <div class="eyebrow"><?= esc($blockTitle) ?></div>
        <h1>Répertoire des salariés</h1>
        <p>Consultation du personnel déjà enregistré dans le système.</p>
    </div>
    <button class="btn btn-white" type="button" <?= $pdfAvailable ? '' : 'disabled title="Aucune bibliothèque PDF n\'est pas installée dans ce projet."' ?>>Exporter en PDF</button>
</div>

<form class="toolbar" method="get" action="<?= site_url('bloc5/dossiers') ?>">
    <input type="search" name="q" value="<?= esc($search, 'attr') ?>" placeholder="Matricule, nom ou prénom" aria-label="Recherche par matricule, nom ou prénom">
    <select name="departement" aria-label="Filtre par département">
        <option value="">Tous les départements</option>
        <?php foreach ($departements as $department): ?>
            <option value="<?= esc((string) ($department['id'] ?? ''), 'attr') ?>" <?= (string) ($department['id'] ?? '') === $departmentFilter ? 'selected' : '' ?>>
                <?= esc($department['nom_departement'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="statut" aria-label="Filtre par statut">
        <option value="">Tous les statuts</option>
        <option value="actif" <?= $statusFilter === 'actif' ? 'selected' : '' ?>>Actif</option>
        <option value="inactif" <?= $statusFilter === 'inactif' ? 'selected' : '' ?>>Inactif</option>
    </select>
    <button class="btn btn-secondary" type="submit">Rechercher</button>
    <?php if ($search !== '' || $departmentFilter !== '' || $statusFilter !== ''): ?>
        <a class="btn btn-white" href="<?= site_url('bloc5/dossiers') ?>">Réinitialiser</a>
    <?php endif; ?>
</form>

<?php
$summary = $total ?? 0;
?>
<?= ui_panel('Liste des salariés', ui_table(['Matricule', 'Nom et prénom', 'Département', 'Poste actuel', 'Date d’embauche', 'Statut', 'Action'], $rows, 'Aucun salarié ne correspond aux filtres sélectionnés.')) ?>

<div class="panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Résultats</h2>
            <p class="panel-sub">Affichage de <?= $pagination['start'] ?> à <?= $pagination['end'] ?> sur <?= $summary ?> salarié(s).</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="pagination" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <?php if ($pagination['hasPrev']): ?>
                <?php $prevQuery = http_build_query(['q' => $search, 'departement' => $departmentFilter, 'statut' => $statusFilter, 'page' => $pagination['currentPage'] - 1]); ?>
                <a class="btn btn-white" href="<?= site_url('bloc5/dossiers') ?>?<?= esc($prevQuery, 'attr') ?>">Précédent</a>
            <?php endif; ?>

            <?php foreach ($pagination['pages'] as $pageNumber): ?>
                <?php $pageQuery = http_build_query(['q' => $search, 'departement' => $departmentFilter, 'statut' => $statusFilter, 'page' => $pageNumber]); ?>
                <a class="btn <?= $pageNumber === $pagination['currentPage'] ? 'btn-primary' : 'btn-white' ?>" href="<?= site_url('bloc5/dossiers') ?>?<?= esc($pageQuery, 'attr') ?>"><?= esc((string) $pageNumber) ?></a>
            <?php endforeach; ?>

            <?php if ($pagination['hasNext']): ?>
                <?php $nextQuery = http_build_query(['q' => $search, 'departement' => $departmentFilter, 'statut' => $statusFilter, 'page' => $pagination['currentPage'] + 1]); ?>
                <a class="btn btn-white" href="<?= site_url('bloc5/dossiers') ?>?<?= esc($nextQuery, 'attr') ?>">Suivant</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
