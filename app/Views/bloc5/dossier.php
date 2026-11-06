<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$employee = $employee ?? null;
if ($employee !== null) {
    $photoSrc = ! empty($employee['photo'])
        ? (str_contains((string) $employee['photo'], 'http') || str_contains((string) $employee['photo'], 'data:') ? (string) $employee['photo'] : base_url((string) $employee['photo']))
        : base_url('assets/images/default-avatar.png');
}
?>
<div class="page-head">
    <div>
        <div class="eyebrow"><?= esc($blockTitle) ?></div>
        <h1>Dossier administratif</h1>
        <p>Consultation du dossier individuel du salarié.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a class="btn btn-white" href="<?= site_url('bloc5/dossiers') ?>">Retour au répertoire</a>
        <?php if ($employee !== null): ?>
            <a class="btn btn-secondary" href="<?= site_url('bloc5/documents/' . (int) ($employee['id'] ?? 0)) ?>">Gestion des documents</a>
            <a class="btn btn-secondary" href="<?= site_url('bloc5/carriere/' . (int) ($employee['id'] ?? 0)) ?>">Voir la carrière</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($employee !== null): ?>
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Fiche du salarié</h2>
                <p class="panel-sub">Vue détaillée de l’identité, des coordonnées et de la situation professionnelle.</p>
            </div>
        </div>
        <div class="panel-body" style="display:grid;grid-template-columns:120px 1fr;gap:24px;align-items:start;">
            <img src="<?= esc($photoSrc, 'attr') ?>" alt="Photo du salarié" style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:1px solid #d1d5db;background:#f3f4f6;">
            <div>
                <h3 style="margin:0 0 10px;">
                    <?= esc($employee['nom'] ?? '—') ?> <?= esc($employee['prenom'] ?? '—') ?>
                </h3>
                <p style="margin:0 0 8px;"><strong>Matricule :</strong> <?= esc($employee['matricule'] ?? '—') ?>
                    &nbsp;·&nbsp; <strong>Statut :</strong> <?= esc($employee['statut'] ?? '—') ?></p>
                <p style="margin:0;">
                    <strong>Département :</strong> <?= esc($employee['departement'] ?? '—') ?><br>
                    <strong>Poste actuel :</strong> <?= esc($employee['poste_actuel'] ?? '—') ?><br>
                    <strong>Date d’embauche :</strong> <?= esc($employee['date_embauche'] ?? '—') ?>
                </p>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Identité et coordonnées</h2>
            </div>
        </div>
        <div class="panel-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
                <div><strong>Email</strong><br><?= esc($employee['email'] ?? '—') ?></div>
                <div><strong>Téléphone</strong><br><?= esc($employee['telephone'] ?? '—') ?></div>
                <div style="grid-column:1 / -1;"><strong>Adresse</strong><br><?= nl2br(esc($employee['adresse'] ?? '—')) ?></div>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Données personnelles</h2>
            </div>
        </div>
        <div class="panel-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                <div><strong>Date de naissance</strong><br><?= esc($employee['date_naissance'] ?? '—') ?></div>
                <div><strong>Situation familiale</strong><br><?= esc($employee['situation_familiale'] ?? '—') ?></div>
                <div><strong>Numéro CNI</strong><br><?= esc($employee['numero_cni'] ?? '—') ?></div>
                <div><strong>Date de délivrance CNI</strong><br><?= esc($employee['date_delivrance_cni'] ?? '—') ?></div>
                <div><strong>Lieu de délivrance CNI</strong><br><?= esc($employee['lieu_delivrance_cni'] ?? '—') ?></div>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Informations professionnelles</h2>
            </div>
        </div>
        <div class="panel-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                <div><strong>Département</strong><br><?= esc($employee['departement'] ?? '—') ?></div>
                <div><strong>Poste actuel</strong><br><?= esc($employee['poste_actuel'] ?? '—') ?></div>
                <div><strong>Date d’embauche</strong><br><?= esc($employee['date_embauche'] ?? '—') ?></div>
                <div><strong>Statut</strong><br><?= esc($employee['statut'] ?? '—') ?></div>
            </div>
        </div>
    </section>
<?php else: ?>
    <?= ui_notice($message ?? 'Aucun salarié n’a été sélectionné.', 'warn') ?>
<?php endif; ?>
<?= $this->endSection() ?>
