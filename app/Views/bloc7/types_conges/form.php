<?php $t = $typeConge ?? []; $action = empty($t['id']) ? 'bloc7/types-conges/enregistrer' : 'bloc7/types-conges/' . $t['id'] . '/enregistrer'; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><h1><?= empty($t['id']) ? 'Créer un type de congé' : 'Modifier le type de congé' ?></h1><p>Paramètres métier sans droits légaux arbitraires.</p></div><a class="btn btn-white" href="<?= site_url('bloc7/types-conges') ?>">Retour</a></div>
<section class="panel"><div class="panel-body"><form method="post" action="<?= site_url($action) ?>"><?= csrf_field() ?><div class="grid2">
<div class="field"><label>Code unique *</label><input name="code" maxlength="30" pattern="[A-Z0-9_-]{2,30}" value="<?= esc(old('code', $t['code'] ?? ''), 'attr') ?>" required></div>
<div class="field"><label>Libellé *</label><input name="libelle" maxlength="120" value="<?= esc(old('libelle', $t['libelle'] ?? ''), 'attr') ?>" required></div>
<div class="field"><label>Mode de décompte</label><select name="mode_decompte"><option value="">Non défini</option><?php foreach (['A_DEFINIR','JOURS_OUVRES','JOURS_OUVRABLES','JOURS_CALENDAIRES'] as $v): ?><option <?= old('mode_decompte', $t['mode_decompte'] ?? '') === $v ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach ?></select></div>
<div class="field"><input type="hidden" name="justificatif_obligatoire" value="0"><label><input type="checkbox" name="justificatif_obligatoire" value="1" <?= in_array(old('justificatif_obligatoire', $t['justificatif_obligatoire'] ?? false), [true,1,'1','t','true'], true) ? 'checked' : '' ?>> Justificatif obligatoire</label><input type="hidden" name="actif" value="0"><label><input type="checkbox" name="actif" value="1" <?= in_array(old('actif', $t['actif'] ?? true), [true,1,'1','t','true'], true) ? 'checked' : '' ?>> Actif</label></div>
</div><div class="field"><label>Description</label><textarea name="description"><?= esc(old('description', $t['description'] ?? '')) ?></textarea></div><button class="btn btn-primary">Enregistrer</button></form></div></section>
<?= $this->endSection() ?>
