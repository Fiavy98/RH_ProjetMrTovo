<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 1 · HISTORIQUE</div><h1>Historique des demandes</h1><p>Consultez le détail et les décisions de chaque demande.</p></div></div>
<section class="panel"><div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Référence</th><th>Intitulé</th><th>Statut</th><th>Date</th></tr></thead><tbody>
<?php if ($requests === []): ?><tr><td colspan="4">Aucune demande enregistrée.</td></tr><?php endif; ?>
<?php foreach ($requests as $request): ?><tr><td><a href="<?= site_url('bloc1/history/' . $request->id) ?>">D-<?= esc(str_pad((string) $request->id, 3, '0', STR_PAD_LEFT)) ?></a></td><td><?= esc($request->intitule) ?></td><td><?= esc($request->status_libelle) ?></td><td><?= esc(date('d/m/Y', strtotime((string) $request->date_demande))) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></section>
<?= $this->endSection() ?>
