<?= $this->extend('layouts/main') ?><?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">FICHE CANDIDAT</div><h1><?= esc($candidate['prenom'].' '.$candidate['nom']) ?></h1><p><?= esc($candidate['email']) ?></p></div><div class="action-row"><a class="btn btn-secondary" href="<?= site_url('bloc2/candidates/'.$candidate['id'].'/edit') ?>">Modifier</a><a class="btn btn-primary" href="<?= site_url('bloc2/applications/new?candidate='.$candidate['id']) ?>">Ajouter une candidature</a></div></div>
<?= ui_panel('Coordonnées','<dl class="detail-grid"><div class="detail"><dt>Téléphone</dt><dd>'.esc($candidate['telephone']??'—').'</dd></div><div class="detail"><dt>CV</dt><dd>'.esc($candidate['cv']??'—').'</dd></div><div class="detail"><dt>Créé le</dt><dd>'.ui_date($candidate['date_creation']).'</dd></div></dl>') ?>
<?php $rows=[];foreach($applications as $a){$rows[]=[$a['offre_titre'],ui_date($a['date_candidature']),ui_raw(ui_badge($a['statut']))];} ?>
<?= ui_panel('Historique des candidatures',ui_table(['Offre','Date','Statut'],$rows,'Aucune candidature.')) ?>
<?= $this->endSection() ?>
