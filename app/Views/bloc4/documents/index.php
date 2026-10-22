<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · DOCUMENTS</div><h1>Documents des salariés</h1><p>Les fichiers sont stockés dans le répertoire privé de l'application ; la base conserve leurs métadonnées.</p></div><div class="action-row"><a class="btn btn-primary" href="<?= site_url('bloc4/document-form') ?>">＋ Ajouter un document</a></div></div>
<?php $rows = []; foreach ($documents as $document) $rows[] = [esc($document['matricule']), esc($document['prenom'] . ' ' . $document['nom']), esc(ui_status_label($document['type_document'])), esc($document['nom_fichier']), ui_date($document['cree_le']), ui_raw('<a class="btn btn-secondary" href="' . site_url('bloc4/documents/' . $document['id'] . '/download') . '">Télécharger</a>')]; ?>
<?= ui_panel('Documents enregistrés', ui_table(['Matricule', 'Salarié', 'Type', 'Nom du fichier', 'Ajouté le', 'Action'], $rows, 'Aucun document enregistré.')) ?>
<?= $this->endSection() ?>
