<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · CONTRATS</div><h1>Contrat #<?= esc($contrat['id']) ?></h1><p><?= esc($contrat['type_code'] . ' · ' . $contrat['prenom'] . ' ' . $contrat['nom']) ?></p></div><div class="action-row"><a class="btn btn-secondary" href="<?= site_url('bloc4/contracts') ?>">← Liste</a><?php if ($contrat['statut'] === 'brouillon'): ?><a class="btn btn-secondary" href="<?= site_url('bloc4/contract-form/' . $contrat['id']) ?>">Modifier</a><?php endif; ?></div></div>
<?= ui_panel('Données du contrat', ui_table(['Champ', 'Valeur'], [
    ['Salarié', esc($contrat['matricule'] . ' · ' . $contrat['prenom'] . ' ' . $contrat['nom'])],
    ['Type', esc($contrat['type_code'] . ' · ' . $contrat['type_libelle'])],
    ['Début', ui_date($contrat['date_debut'])], ['Fin', ui_date($contrat['date_fin'])],
    ['Salaire', ui_money($contrat['salaire'])], ['Date de signature', ui_date($contrat['date_signature'])],
    ['Statut', ui_raw(ui_badge($contrat['statut']))],
    ['Contrat précédent', $contrat['precedent'] ? ui_raw('<a href="' . site_url('bloc4/contract-detail/' . $contrat['precedent']['id']) . '">Contrat #' . esc($contrat['precedent']['id']) . '</a>') : '—'],
])) ?>
<div class="panel"><div class="panel-head"><div><h2 class="panel-title">Actions</h2><p class="panel-sub">Les transitions disponibles dépendent de l’état et des dates du contrat.</p></div></div><div class="panel-body"><div class="action-row">
    <?php
    $actions = match ($contrat['statut']) {
        'brouillon' => ['signer' => 'Signer le contrat', 'annuler' => 'Annuler'],
        'signe' => ['demarrer' => 'Passer en cours', 'annuler' => 'Annuler'],
        'en_cours' => ['terminer' => 'Terminer le contrat'],
        default => [],
    };
    foreach ($actions as $action => $label): ?>
        <form method="post" action="<?= site_url('bloc4/contract-detail/' . $contrat['id'] . '/' . $action) ?>"><?= csrf_field() ?><button class="btn <?= $action === 'annuler' ? 'btn-danger' : 'btn-primary' ?>" type="submit"><?= esc($label) ?></button></form>
    <?php endforeach; ?>
    <?php if ($contrat['statut'] === 'termine' && $contrat['date_fin'] !== null && $contrat['contrat_precedent_id'] === null && $contrat['renouvellements'] === []): ?><a class="btn btn-secondary" href="<?= site_url('bloc4/renewals/' . $contrat['id']) ?>">Renouveler</a><?php endif; ?>
</div></div></div>
<?= ui_panel('Historique des renouvellements', ui_table(['Contrat', 'Type', 'Début', 'Fin', 'Statut'], array_map(static fn ($item) => [
    ui_raw('<a href="' . site_url('bloc4/contract-detail/' . $item['id']) . '">#' . esc($item['id']) . '</a>'), esc($item['type_code']), ui_date($item['date_debut']), ui_date($item['date_fin']), ui_raw(ui_badge($item['statut'])),
], $contrat['renouvellements']), 'Aucun renouvellement enregistré.')) ?>
<div class="action-row" style="margin:18px 0"><a class="btn btn-secondary" href="<?= site_url('bloc4/document-form') ?>">＋ Ajouter un document</a></div>
<?= ui_panel('Documents rattachés', ui_table(['Type', 'Nom du fichier', 'Ajouté le', 'Téléchargement'], array_map(static fn ($doc) => [esc(ui_status_label($doc['type_document'])), esc($doc['nom_fichier']), ui_date($doc['cree_le']), ui_raw('<a class="btn btn-secondary" href="' . site_url('bloc4/documents/' . $doc['id'] . '/download') . '">Télécharger</a>')], $contrat['documents']), 'Aucun document rattaché.')) ?>
<?= $this->endSection() ?>
