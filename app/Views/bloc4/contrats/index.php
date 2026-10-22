<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · CONTRATS</div><h1>Liste des contrats</h1><p>Consultez les périodes contractuelles et ouvrez leur fiche de suivi.</p></div><div class="action-row"><a class="btn btn-primary" href="<?= site_url('bloc4/contract-form') ?>">＋ Nouveau contrat</a></div></div>
<form class="toolbar" method="get">
    <select name="type"><option value="">Tous les types</option><?php foreach ($types as $type): ?><option value="<?= esc($type['code'], 'attr') ?>" <?= ($filters['type'] ?? '') === $type['code'] ? 'selected' : '' ?>><?= esc($type['code'] . ' · ' . $type['libelle']) ?></option><?php endforeach; ?></select>
    <select name="statut"><option value="">Tous les statuts</option><?php foreach (['brouillon', 'signe', 'en_cours', 'termine', 'annule'] as $status): ?><option value="<?= esc($status, 'attr') ?>" <?= ($filters['statut'] ?? '') === $status ? 'selected' : '' ?>><?= esc(ui_status_label($status)) ?></option><?php endforeach; ?></select>
    <input type="search" name="employe" value="<?= esc($filters['employe'] ?? '', 'attr') ?>" placeholder="Matricule ou nom du salarié">
    <button class="btn btn-secondary" type="submit">Filtrer</button>
</form>
<?php
$rows = [];
foreach ($contrats as $contrat) {
    $rows[] = [
        esc($contrat['matricule']), esc($contrat['prenom'] . ' ' . $contrat['nom']), esc($contrat['type_code']),
        ui_date($contrat['date_debut']), ui_date($contrat['date_fin']), ui_raw(ui_badge($contrat['statut'])),
        ui_raw('<a class="btn btn-secondary" href="' . site_url('bloc4/contract-detail/' . $contrat['id']) . '">Ouvrir</a>'),
    ];
}
echo ui_panel('Contrats enregistrés', ui_table(['Matricule', 'Salarié', 'Type', 'Début', 'Fin', 'Statut', 'Action'], $rows, 'Aucun contrat ne correspond à ces filtres.'));
?>
<?= $this->endSection() ?>
