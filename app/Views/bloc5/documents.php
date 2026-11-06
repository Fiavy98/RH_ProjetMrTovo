<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$employee = $employee ?? null;
$documents = $documents ?? [];
$typeFilter = (string) ($filter ?? '');
$message = $message ?? session()->getFlashdata('message');
$error = $error ?? session()->getFlashdata('error');
$documentTypes = $types ?? ['contrat_signe' => 'Contrat signé', 'piece_identite' => 'Pièce d’identité', 'autre' => 'Autre'];
$contracts = $contracts ?? [];
$search = trim((string) ($search ?? ''));
$searchResults = $searchResults ?? [];
?>
<div class="page-head">
    <div>
        <div class="eyebrow"><?= esc($blockTitle) ?></div>
        <h1>Documents administratifs</h1>
        <?php if ($employee !== null): ?>
            <p><?= esc($employee['nom_complet'] ?? '') ?> · Matricule : <?= esc($employee['matricule'] ?? '—') ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php if ($employee !== null): ?>
            <a class="btn btn-white" href="<?= site_url('bloc5/dossier/' . (int) ($employee['id'] ?? 0)) ?>">Retour au dossier</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($message): ?>
    <?= ui_notice((string) $message, 'success') ?>
<?php endif; ?>
<?php if ($error): ?>
    <?= ui_notice((string) $error, 'error') ?>
<?php endif; ?>

<?php if ($employee === null): ?>
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Choisir un salarié</h2>
                <p class="panel-sub">Rechercher par nom, prénom ou matricule.</p>
            </div>
        </div>
        <div class="panel-body">
            <form method="get" action="<?= site_url('bloc5/documents') ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
                <div class="field" style="min-width:300px;">
                    <label for="employee-search">Salarié</label>
                    <input id="employee-search" name="q" type="search" value="<?= esc($search, 'attr') ?>" placeholder="Nom, prénom ou matricule" autocomplete="off">
                </div>
                <button class="btn btn-secondary" type="submit">Rechercher</button>
            </form>
        </div>
    </section>

    <?php if ($search !== ''): ?>
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Résultats</h2>
                </div>
            </div>
            <div class="panel-body">
                <?php if (empty($searchResults)): ?>
                    <p>Aucun salarié ne correspond à votre recherche.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Matricule</th>
                                <th>Nom et prénom</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $result): ?>
                                <tr>
                                    <td><?= esc((string) ($result['matricule'] ?? '—')) ?></td>
                                    <td><?= esc(trim((string) ($result['prenom'] ?? '') . ' ' . (string) ($result['nom'] ?? ''))) ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="<?= site_url('bloc5/documents/' . (int) ($result['id'] ?? 0)) ?>">Sélectionner</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>

<?php if ($employee !== null): ?>
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Filtrer les documents</h2>
            </div>
        </div>
        <div class="panel-body">
            <form method="get" action="<?= site_url('bloc5/documents/' . (int) ($employee['id'] ?? 0)) ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
                <div class="field" style="min-width:220px;">
                    <label for="document-type-filter">Type</label>
                    <select name="type" id="document-type-filter">
                        <option value="">Tous</option>
                        <?php foreach ($documentTypes as $code => $label): ?>
                            <option value="<?= esc((string) $code, 'attr') ?>" <?= $typeFilter === (string) $code ? 'selected' : '' ?>><?= esc((string) $label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-secondary" type="submit">Filtrer</button>
                <?php if ($typeFilter !== ''): ?>
                    <a class="btn btn-white" href="<?= site_url('bloc5/documents/' . (int) ($employee['id'] ?? 0)) ?>">Tout afficher</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Ajouter un document</h2>
            </div>
        </div>
        <div class="panel-body">
            <form method="post" action="<?= site_url('bloc5/documents/' . (int) ($employee['id'] ?? 0) . '/store') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="field">
                        <label for="document-name">Nom du document</label>
                        <input id="document-name" name="nom_fichier" type="text" required placeholder="Ex. Contrat 2026">
                    </div>
                    <div class="field">
                        <label for="document-type">Type de document</label>
                        <select id="document-type" name="type_document" required>
                            <?php foreach ($documentTypes as $code => $label): ?>
                                <option value="<?= esc((string) $code, 'attr') ?>"><?= esc((string) $label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="document-contrat">Contrat associé</label>
                        <select id="document-contrat" name="contrat_id">
                            <option value="">Aucun contrat</option>
                            <?php foreach ($contracts as $contract): ?>
                                <option value="<?= esc((string) ($contract['id'] ?? ''), 'attr') ?>">
                                    <?= esc((string) ($contract['type_code'] ?? 'Contrat')) ?> · <?= esc((string) ($contract['date_debut'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="document-date-emission">Date d’émission</label>
                        <input id="document-date-emission" name="date_emission" type="date">
                    </div>
                    <div class="field">
                        <label for="document-date-expiration">Date d’expiration</label>
                        <input id="document-date-expiration" name="date_expiration" type="date">
                    </div>
                    <div class="field">
                        <label for="document-file">Fichier</label>
                        <input id="document-file" name="fichier" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.txt" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Ajouter</button>
                </div>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Liste des documents</h2>
            </div>
        </div>
        <div class="panel-body">
            <?php if (empty($documents)): ?>
                <p>Aucun document ne correspond au filtre sélectionné.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Fichier</th>
                        <th>Émission</th>
                        <th>Expiration</th>
                        <th>Date d’ajout</th>
                        <th>État</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td><?= esc((string) ($document['nom_fichier'] ?? '—')) ?></td>
                            <td><?= esc((string) ($document['type_label'] ?? '—')) ?></td>
                            <td><?= esc((string) ($document['nom_fichier'] ?? '—')) ?></td>
                            <td><?= esc((string) ($document['date_emission'] ?? '—')) ?></td>
                            <td><?= esc((string) ($document['date_expiration'] ?? '—')) ?></td>
                            <td><?= esc((string) ($document['cree_le'] ?? '—')) ?></td>
                            <td>
                                <?php
                                $stateClass = 'neutral';
                                if ($document['etat'] === 'Valide') { $stateClass = 'success'; }
                                elseif ($document['etat'] === 'Bientôt expiré') { $stateClass = 'warning'; }
                                elseif ($document['etat'] === 'Expiré') { $stateClass = 'danger'; }
                                ?>
                                <span class="status <?= esc($stateClass, 'attr') ?>"><?= esc((string) ($document['etat'] ?? '—')) ?></span>
                            </td>
                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                    <a class="btn btn-secondary" href="<?= site_url('bloc5/documents/' . (int) ($employee['id'] ?? 0) . '/download/' . (int) ($document['id'] ?? 0)) ?>">Télécharger</a>
                                    <form method="post" action="<?= site_url('bloc5/documents/' . (int) ($employee['id'] ?? 0) . '/delete/' . (int) ($document['id'] ?? 0)) ?>" onsubmit="return confirm('Supprimer ce document ?');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-danger" type="submit">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?= $this->endSection() ?>
