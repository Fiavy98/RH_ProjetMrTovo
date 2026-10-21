<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <div class="eyebrow">Développement</div>
        <h1>Contrôle de la base</h1>
        <p>Vérifie que chaque Model lit sa table. Page visible uniquement hors production.</p>
    </div>
</div>

<?php if ($dbError !== null): ?>
    <?= ui_notice('Erreur : ' . $dbError, 'danger') ?>
<?php else: ?>
    <?php
    $rows = [];
    foreach ($counts as $table => $total) {
        $rows[] = [$table, (string) $total, ui_raw($total > 0 ? ui_badge('OUI') : '<span class="badge gray">Vide</span>')];
    }

    $employeRows = [];
    foreach ($employes as $e) {
        $employeRows[] = [
            $e->matricule,
            $e->prenom . ' ' . $e->nom,
            $e->email,
            ui_date($e->date_embauche),
            ui_raw(ui_badge($e->actif ? 'OUI' : 'NON')),
        ];
    }
    ?>
    <div class="split">
        <?= ui_panel('Lignes par table', ui_table(['Table', 'Lignes', 'Données de test'], $rows), 'Lecture via les 21 Models.') ?>
        <?= ui_panel(
            'Salariés (Entity Employe)',
            ui_table(['Matricule', 'Nom', 'Email', 'Embauche', 'Actif'], $employeRows, 'Aucun salarié : exécuter db_SIRH_donnees_test.sql.'),
            'Contrôle des casts : date lisible, booléen correct.'
        ) ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
