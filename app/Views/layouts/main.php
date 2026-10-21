<?php
/**
 * Layout commun (structure du template NovaRH : menu latéral + barre supérieure + zone de contenu).
 * Une vue l'utilise avec :  <?= $this->extend('layouts/main') ?>  puis  <?= $this->section('content') ?> ... <?= $this->endSection() ?>
 * Variable optionnelle : $pageTitle (titre affiché dans le fil d'Ariane et l'onglet).
 */
$roles       = ui_roles();
$currentRole = ui_role();
$me          = $roles[$currentRole];
$currentPath = trim(uri_string(), '/');
$pageTitle   = $pageTitle ?? 'Tableau de bord';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#14233e">
    <title><?= esc($pageTitle) ?> | NovaRH</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/sirh.css') ?>">
</head>
<body>
<div class="mobile-mask" id="mobile-mask" data-action="mobile-close"></div>
<div class="app-layout" id="app-layout">

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark">N</div>
            <div><div class="brand-name">NovaRH</div><small>Gestion des talents</small></div>
        </div>

        <?php foreach (ui_menu() as $group): ?>
            <div class="nav-group">
                <button type="button" class="nav-heading" data-group>
                    <span><?= esc($group['title']) ?></span><span>⌄</span>
                </button>
                <div class="nav-items">
                    <?php foreach ($group['items'] as $item):
                        $url    = trim($item['url'], '/');
                        $active = $url === '' ? $currentPath === '' : str_starts_with($currentPath, $url);
                    ?>
                        <a class="nav-link <?= $active ? 'active' : '' ?>" href="<?= site_url($url) ?>" title="<?= esc($item['label'], 'attr') ?>">
                            <span class="nav-ic"><?= esc($item['icon'] ?? '•') ?></span>
                            <span class="nav-label"><?= esc($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="nav-foot">● Mode développement<br>Rôles de démonstration, sans sécurité réelle.</div>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <div class="top-left">
                <button type="button" class="icon-btn mobile-toggle" data-action="mobile-toggle" aria-label="Ouvrir le menu">☰</button>
                <button type="button" class="icon-btn" data-action="collapse" aria-label="Réduire le menu">☷</button>
                <div class="breadcrumb">Espace RH / <strong><?= esc($pageTitle) ?></strong></div>
            </div>
            <div class="top-right">
                <span class="badge blue">DÉMO</span>
                <?php if (ENVIRONMENT !== 'production'): ?>
                    <form method="post" action="<?= site_url('dev/role') ?>" class="role-form">
                        <?= csrf_field() ?>
                        <select class="role-switch" name="role" data-role-select aria-label="Changer de rôle de démonstration">
                            <?php foreach ($roles as $key => $role): ?>
                                <option value="<?= esc($key, 'attr') ?>" <?= $key === $currentRole ? 'selected' : '' ?>><?= esc($role['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
                <div class="avatar"><?= esc($me['initials']) ?></div>
                <div>
                    <div class="profile-title"><?= esc($me['name']) ?></div>
                    <div class="profile-sub"><?= esc($me['label']) ?></div>
                </div>
            </div>
        </header>

        <main class="workspace" id="main-content">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<div class="toast-host" id="toast-host">
    <?php foreach (['success' => '', 'error' => 'error'] as $type => $class):
        $message = session()->getFlashdata($type);
        if ($message): ?>
            <div class="toast <?= $class ?>"><?= esc((string) $message) ?></div>
        <?php endif;
    endforeach; ?>
</div>

<script src="<?= base_url('assets/js/sirh.js') ?>" defer></script>
</body>
</html>
