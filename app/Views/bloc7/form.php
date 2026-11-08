<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $item=$item??[]; $options=$options??['employes'=>[],'types'=>[]]; $employeeOptions=[]; foreach($options['employes'] as $e)$employeeOptions[$e['id']]=$e['matricule'].' · '.$e['prenom'].' '.$e['nom']; $typeOptions=[]; foreach($options['types'] as $t)$typeOptions[$t['id']]=$t['libelle'].' (jours ouvrés)'; $formAction=isset($item['id'])?site_url('bloc7/demandes/'.$item['id'].'/modifier'):site_url('bloc7/demandes/nouvelle'); ?>
<div class="page-head"><div><p class="eyebrow">Congés et absences</p><h1><?= esc($title) ?></h1><p><?= ui_role()==='salarie'?'Saisissez votre propre demande.':'Choisissez le salarié concerné et la période demandée.' ?></p></div></div>
<form class="panel" method="post" enctype="multipart/form-data" action="<?= $formAction ?>"><?= csrf_field() ?><div class="panel-body"><div class="form-grid">
<?php if(ui_role()==='salarie'): ?><input type="hidden" name="employe_id" value="<?= (int)session()->get('auth_employee_id') ?>"><?php else: ?>
<?= ui_field('employe_id','Salarié concerné','', $item['employe_id']??'', ['required'=>true,'options'=>$employeeOptions]) ?>
<?php endif; ?>
<?= ui_field('type_conge_id','Type de congé','', $item['type_conge_id']??'', ['required'=>true,'options'=>$typeOptions]) ?>
<?= ui_field('date_debut','Date de début','date',$item['date_debut']??'', ['required'=>true]) ?>
<?= ui_field('portion_debut','Portion de début','',$item['portion_debut']??'journee',['required'=>true,'options'=>['journee'=>'Journée entière','matin'=>'Matin','apres_midi'=>'Après-midi']]) ?>
<?= ui_field('date_fin','Date de fin','date',$item['date_fin']??'', ['required'=>true]) ?>
<?= ui_field('portion_fin','Portion de fin','',$item['portion_fin']??'journee',['required'=>true,'options'=>['journee'=>'Journée entière','matin'=>'Matin','apres_midi'=>'Après-midi']]) ?>
<div class="field full"><label for="f-motif">Motif / commentaire</label><textarea id="f-motif" name="motif"><?= esc(old('motif',$item['motif']??'')) ?></textarea></div>
<div class="field full"><label for="f-justificatif">Justificatif (facultatif)</label><input id="f-justificatif" type="file" name="justificatif" accept=".pdf,.jpg,.jpeg,.png"><small>Vous pouvez soumettre la demande sans pièce jointe. Si vous en ajoutez une : PDF, JPEG ou PNG, 8 Mo maximum. Les fichiers sont accessibles uniquement à la RH.</small></div>
</div><div class="form-actions"><a class="btn" href="<?= site_url('bloc7/demandes') ?>">Retour</a><button class="btn primary" type="submit">Enregistrer le brouillon</button></div></div></form>
<?= $this->endSection() ?>
