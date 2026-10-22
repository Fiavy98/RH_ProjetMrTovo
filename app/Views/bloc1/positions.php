<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 1 · POSTES</div>
        <h1>Postes associés</h1>
        <p>Liste des postes créés à partir des demandes approuvées.</p>
    </div>
</div>
<section class="panel">
    <div class="panel-head"><h2 class="panel-title">Liste des postes</h2></div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Poste</th>
                        <th>Demande</th>
                        <th>Département</th>
                        <th>Statut</th>
                        <th>Description</th>
                        <th>Compétences requises</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($positions === []): ?>
                        <tr><td colspan="6">Aucun poste associé n’a encore été créé.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($positions as $position): ?>
                        <tr>
                            <td><?= esc($position->intitule) ?></td>
                            <td><a href="<?= site_url('bloc1/history/' . $position->demande_id_ref) ?>">D-<?= esc(str_pad((string) $position->demande_id_ref, 3, '0', STR_PAD_LEFT)) ?></a><br><small><?= esc($position->demande_intitule) ?></small></td>
                            <td><?= esc($position->nom_departement) ?></td>
                            <td><?= esc($position->status_libelle) ?></td>
                            <td><?= $position->description !== null && $position->description !== '' ? nl2br(esc($position->description)) : '—' ?></td>
                            <td><?= $position->competences_requises !== null && $position->competences_requises !== '' ? nl2br(esc($position->competences_requises)) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
