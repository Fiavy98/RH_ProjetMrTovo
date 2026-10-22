<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · PARAMÈTRES</div><h1>Paramètres des contrats</h1><p>Les règles sont lues depuis la table de référence `type_contrat`.</p></div></div>
<?= ui_notice('Les règles retenues sont CE : 6 mois maximum par période et un renouvellement ; CDD : 12 mois maximum par période et un renouvellement ; CDI : sans date de fin et sans renouvellement. Les rôles et l’édition des paramètres seront raccordés à la gestion commune ultérieurement.') ?>
<?php $rows = []; foreach ($types as $type) $rows[] = [esc($type['code']), esc($type['libelle']), $type['duree_max_mois'] === null ? 'Sans limite' : esc((string) $type['duree_max_mois'] . ' mois'), esc((string) $type['nb_renouvellements_max'])]; ?>
<?= ui_panel('Types de contrat configurés', ui_table(['Code', 'Libellé', 'Durée maximale par période', 'Renouvellements maximum'], $rows, 'Aucun type de contrat paramétré. Exécutez le script SQL de référence.')) ?>
<?= $this->endSection() ?>
