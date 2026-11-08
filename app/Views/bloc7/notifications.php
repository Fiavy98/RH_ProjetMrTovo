<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><p class="eyebrow">Congés et absences</p><h1>Mes notifications</h1></div>
<form method="post" action="<?= site_url('bloc7/notifications/lues') ?>"><?= csrf_field() ?><button class="btn">Tout marquer comme lu</button></form></div>
<section class="panel"><div class="panel-body"><ul><?php foreach($notifications as $notification): ?><li class="<?= $notification['lue_le']?'':'notice' ?>"><b><?= esc($notification['titre']) ?></b> — <?= esc($notification['message']) ?> <small><?= ui_date($notification['cree_le']) ?></small><?php if($notification['demande_conge_id']): ?> · <a href="<?= site_url('bloc7/demandes/'.$notification['demande_conge_id']) ?>">Voir la demande</a><?php endif; ?></li><?php endforeach; ?><?php if(!$notifications): ?><li>Aucune notification.</li><?php endif; ?></ul></div></section>
<?= $this->endSection() ?>
