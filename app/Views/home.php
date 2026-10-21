<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <div class="eyebrow">Accueil</div>
        <h1>Bonjour 👋</h1>
        <p>Socle CodeIgniter 4 du SIRH : layout, composants et Models de tous les blocs. Chaque bloc ajoute ses écrans dans son propre dossier.</p>
    </div>
</div>

<?php if ($dbError !== null): ?>
    <?= ui_notice('Connexion à la base impossible : ' . $dbError . ' — vérifier le fichier .env et que les scripts SQL ont été exécutés.', 'danger') ?>
<?php else: ?>
    <div class="cards">
        <?php foreach ($stats as [$label, $icon, $total]): ?>
            <div class="stat">
                <div class="stat-ico"><?= esc($icon) ?></div>
                <div class="num"><?= esc((string) $total) ?></div>
                <div class="label"><?= esc($label) ?></div>
                <div class="hint">Lues en base via les Models</div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= ui_panel(
    'Où coder ?',
    ui_table(
        ['Élément', 'Emplacement (remplacer N par le numéro du bloc)'],
        [
            ['Controllers', 'app/Controllers/BlocN/'],
            ['Routes', 'app/Config/Routes/blocN.php'],
            ['Entrées de menu', 'app/Config/Menu/blocN.php'],
            ['Vues', 'app/Views/blocN/ (layout : layouts/main)'],
            ['Règles métier (Services)', 'app/Services/BlocN/'],
            ['Models et Entities', 'app/Models/ et app/Entities/ (une paire par table, partagés)'],
        ]
    ),
    'Chacun travaille dans le dossier de son bloc pour éviter les conflits Git.'
) ?>
<?= $this->endSection() ?>
