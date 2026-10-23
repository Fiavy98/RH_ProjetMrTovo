<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 2</div><h1>Offres d'emploi</h1><p>Chaque offre est rattachée à un poste précis.</p></div><a class="btn btn-primary" href="<?= site_url('bloc2/offers/new') ?>">Nouvelle offre</a></div>
<form class="toolbar" method="get"><input name="q" type="search" value="<?= esc((string) service('request')->getGet('q'), 'attr') ?>" placeholder="Titre, demande ou poste…"><select name="statut"><option value="">Tous les statuts</option><?php foreach (['BROUILLON', 'PUBLIEE', 'CLOTUREE'] as $status): ?><option <?= service('request')->getGet('statut') === $status ? 'selected' : '' ?>><?= esc($status) ?></option><?php endforeach ?></select><button class="btn btn-secondary">Filtrer</button></form>
<?php
$rows = [];
foreach ($offers as $offer) {
    $actions = '<div class="action-row"><a class="btn btn-secondary" href="' . site_url('bloc2/offers/' . $offer['id']) . '">Consulter</a>';
    if ($offer['statut'] === 'BROUILLON') $actions .= '<form method="post" action="' . site_url('bloc2/offers/' . $offer['id'] . '/publish') . '">' . csrf_field() . '<button class="btn btn-primary">Publier</button></form>';
    if ($offer['statut'] === 'PUBLIEE') $actions .= '<form method="post" action="' . site_url('bloc2/offers/' . $offer['id'] . '/close') . '">' . csrf_field() . '<button class="btn btn-danger">Clôturer</button></form>';
    $actions .= '<a class="btn btn-white" href="' . site_url('bloc2/applications?offre_emploi_id=' . $offer['id']) . '">Candidatures</a></div>';
    $rows[] = [esc($offer['titre']), esc($offer['poste_intitule'] ?? 'Poste non renseigné'), esc($offer['demande_intitule'] ?? ('Demande #' . $offer['demande_recrutement_id'])), ui_date($offer['date_publication']), ui_date($offer['date_cloture']), ui_raw(ui_badge($offer['statut'])), (string) $offer['candidature_count'], ui_raw($actions)];
}
?>
<?= ui_panel('Liste des offres', ui_table(['Titre', 'Poste', 'Demande', 'Publication', 'Clôture', 'Statut', 'Candidatures', 'Actions'], $rows, 'Aucune offre.')) ?>
<?= $this->endSection() ?>
