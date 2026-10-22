<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 1 · DIRECTION</div><h1>Demandes à traiter</h1><p>Demandes approuvées par les RH et la Finance.</p></div></div>
<section class="panel"><div class="panel-head"><h2 class="panel-title">File Direction</h2></div><div class="panel-body"><div class="table-wrap"><table>
<thead><tr><th>Référence</th><th>Intitulé</th><th>Demandeur</th><th>Département</th><th>Budget</th><th>Date</th></tr></thead>
<tbody>
<?php if ($requests === []): ?><tr><td colspan="6">Aucune demande Direction en attente.</td></tr><?php endif; ?>
<?php foreach ($requests as $request): ?>
<tr><td><a href="<?= site_url('bloc1/direction-review/' . $request->id) ?>">D-<?= esc(str_pad((string) $request->id, 3, '0', STR_PAD_LEFT)) ?></a></td><td><?= esc($request->intitule) ?></td><td><?= esc($request->demandeur_prenom . ' ' . $request->demandeur_nom) ?></td><td><?= esc($request->nom_departement) ?></td><td><?= esc($request->budget_min ?? '—') ?> - <?= esc($request->budget_max ?? '—') ?> Ar</td><td><?= esc(date('d/m/Y', strtotime((string) $request->date_demande))) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></section>
<?= $this->endSection() ?>
