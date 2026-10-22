<?= $this->extend('layouts/main') ?><?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">CANDIDATS</div><h1>Répertoire des candidats</h1><p>Recherche et gestion des fiches candidats.</p></div><a class="btn btn-primary" href="<?= site_url('bloc2/candidates/new') ?>">Nouveau candidat</a></div>
<form class="toolbar"><input type="search" name="q" value="<?= esc((string)service('request')->getGet('q'),'attr') ?>" placeholder="Nom, prénom ou e-mail…"><button class="btn btn-secondary">Rechercher</button></form>
<?php $rows=[];foreach($candidates as $c){$rows[]=[$c['nom'],$c['prenom'],$c['email'],$c['telephone'],ui_date($c['date_creation']),ui_raw('<a class="btn btn-secondary" href="'.site_url('bloc2/candidates/'.$c['id']).'">Ouvrir</a> <a class="btn btn-white" href="'.site_url('bloc2/candidates/'.$c['id'].'/edit').'">Modifier</a>')];} ?>
<?= ui_panel('Candidats',ui_table(['Nom','Prénom','E-mail','Téléphone','Création','Actions'],$rows,'Aucun candidat.')) ?>
<?= $this->endSection() ?>
