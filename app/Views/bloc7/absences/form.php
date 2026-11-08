<?php $a = $absence ?? []; $action = empty($a['id']) ? 'bloc7/absences/enregistrer' : 'bloc7/absences/' . $a['id'] . '/enregistrer'; ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><h1><?= empty($a['id']) ? 'Enregistrer une absence' : 'Modifier l’absence' ?></h1><p>Les congés et soldes ne sont jamais créés ou modifiés automatiquement.</p></div><a class="btn btn-white" href="<?= site_url('bloc7/absences') ?>">Retour</a></div>
<section class="panel"><div class="panel-body"><form method="post" enctype="multipart/form-data" action="<?= site_url($action) ?>"><?= csrf_field() ?>
<div class="grid2">
    <div class="field"><label>Salarié *</label><select name="employe_id" required><option value="">Sélectionner</option><?php foreach ($options['employes'] as $e): ?><option value="<?= (int) $e['id'] ?>" <?= (string) old('employe_id', $a['employe_id'] ?? '') === (string) $e['id'] ? 'selected' : '' ?>><?= esc($e['matricule'] . ' · ' . $e['prenom'] . ' ' . $e['nom']) ?></option><?php endforeach ?></select></div>
    <div class="field"><label>Type de congé / absence</label><select name="type_conge_id"><option value="">Aucun type lié</option><?php foreach ($options['types'] as $t): ?><option value="<?= (int) $t['id'] ?>" <?= (string) old('type_conge_id', $a['type_conge_id'] ?? '') === (string) $t['id'] ? 'selected' : '' ?>><?= esc($t['code'] . ' · ' . $t['libelle']) ?><?= in_array($t['justificatif_obligatoire'], [true, 1, '1', 't', 'true'], true) ? ' (justificatif requis)' : '' ?></option><?php endforeach ?></select></div>
    <div class="field"><label>Date de début *</label><input type="date" name="date_debut" value="<?= esc(old('date_debut', $a['date_debut'] ?? ''), 'attr') ?>" required></div>
    <div class="field"><label>Date de fin *</label><input type="date" name="date_fin" value="<?= esc(old('date_fin', $a['date_fin'] ?? ''), 'attr') ?>" required></div>
    <div class="field"><label>Motif *</label><input name="motif" maxlength="180" value="<?= esc(old('motif', $a['motif'] ?? ''), 'attr') ?>" required></div>
    <div class="field"><label>Statut *</label><select name="statut" required><?php foreach (['ENREGISTREE','A_VERIFIER','JUSTIFIEE','NON_JUSTIFIEE','ANNULEE'] as $v): ?><option <?= old('statut', $a['statut'] ?? 'ENREGISTREE') === $v ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach ?></select></div>
    <div class="field"><label>Demande de congé approuvée liée (ID optionnel)</label><input type="number" min="1" name="demande_conge_id" value="<?= esc(old('demande_conge_id', $a['demande_conge_id'] ?? ''), 'attr') ?>"></div>
    <div class="field"><label>Justificatif (PDF/JPEG/PNG, 5 Mo max.)</label><input type="file" name="justificatif" accept="application/pdf,image/jpeg,image/png"><?php if (! empty($a['justificatif_nom_original'])): ?><small>Actuel : <?= esc($a['justificatif_nom_original']) ?></small><?php endif ?></div>
</div>
<div class="field"><label>Commentaire</label><textarea name="commentaire"><?= esc(old('commentaire', $a['commentaire'] ?? '')) ?></textarea></div>
<button class="btn btn-primary">Enregistrer</button>
</form></div></section>
<?= $this->endSection() ?>
