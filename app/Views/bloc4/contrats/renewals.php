<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · CONTRATS</div><h1>Renouvellements possibles</h1><p>Un seul renouvellement est permis pour un CE ou un CDD. Le renouvellement garde le même salarié et le même type.</p></div><div class="action-row"><a class="btn btn-secondary" href="<?= site_url('bloc4/contracts') ?>">← Contrats</a></div></div>
<?php $rows = []; foreach ($contrats as $contrat) $rows[] = [esc($contrat['matricule']), esc($contrat['prenom'] . ' ' . $contrat['nom']), esc($contrat['type_code']), ui_date($contrat['date_fin']), ui_raw('<a class="btn btn-secondary" href="' . site_url('bloc4/renewals/' . $contrat['id']) . '">Préparer le renouvellement</a>')]; ?>
<?= ui_panel('Contrats éligibles', ui_table(['Matricule', 'Salarié', 'Type', 'Fin de période', 'Action'], $rows, 'Aucun contrat terminé ne peut être renouvelé.')) ?>
<?= $this->endSection() ?>
