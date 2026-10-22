<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 1 · SUIVI</div>
        <h1>Vue des demandes</h1>
        <p>Consultez toutes les demandes et leur statut actuel. Cette page est en lecture seule.</p>
    </div>
</div>
<section class="panel">
    <div class="panel-head"><h2 class="panel-title">Toutes les demandes</h2></div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Intitulé</th>
                        <th>Demandeur</th>
                        <th>Département</th>
                        <th>Type de contrat</th>
                        <th>Statut actuel</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests === []): ?>
                        <tr><td colspan="7">Aucune demande enregistrée.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td>D-<?= esc(str_pad((string) $request->id, 3, '0', STR_PAD_LEFT)) ?></td>
                            <td><?= esc($request->intitule) ?></td>
                            <td><?= esc($request->demandeur_prenom . ' ' . $request->demandeur_nom) ?></td>
                            <td><?= esc($request->nom_departement) ?></td>
                            <td><?= esc($request->type_contrat_libelle ?? '—') ?></td>
                            <td><?= esc($request->status_libelle) ?></td>
                            <td><?= esc(date('d/m/Y', strtotime((string) $request->date_demande))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
