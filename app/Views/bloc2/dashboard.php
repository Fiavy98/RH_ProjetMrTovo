<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 2 · RECRUTEMENT</div><h1>Suivi du recrutement</h1><p>Vue synthétique du pipeline, alimentée par la base.</p></div><div class="action-row"><a class="btn btn-primary" href="<?= site_url('bloc2/offers/new') ?>">Créer une offre</a></div></div>
<div class="cards">
<?php foreach ([['Offres', '▤', $stats['offers']], ['Offres ouvertes', '◈', $stats['open_offers']], ['Candidats', '♙', $stats['candidates']], ['Candidatures', '◇', $stats['applications']], ['Entretiens planifiés', '▷', $stats['interviews']], ['Candidats retenus', '✓', $stats['retained']]] as [$label, $icon, $value]): ?>
<div class="stat"><div class="stat-ico"><?= esc($icon) ?></div><div class="num"><?= esc((string) $value) ?></div><div class="label"><?= esc($label) ?></div><div class="hint">Données persistantes</div></div>
<?php endforeach ?>
</div>
<?= ui_panel('Workflow', '<p class="muted">Demande approuvée → Offre → Candidature → Présélection → Entretien → Évaluation → Décision finale. Une candidature retenue reste disponible pour le Bloc 3 ; aucun salarié n’est créé ici.</p>') ?>
<?= $this->endSection() ?>
