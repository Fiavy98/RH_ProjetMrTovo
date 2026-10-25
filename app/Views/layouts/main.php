<?php
/**
 * Layout commun (structure du template NovaRH : menu latéral + barre supérieure + zone de contenu).
 * Une vue l'utilise avec :  <?= $this->extend('layouts/main') ?>  puis  <?= $this->section('content') ?> ... <?= $this->endSection() ?>
 * Variable optionnelle : $pageTitle (titre affiché dans le fil d'Ariane et l'onglet).
 */
$me          = ui_current_user();
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

        <div class="nav-foot">● Mode développement<br>Sélection d’utilisateur sans mot de passe.</div>
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
                <div class="avatar"><?= esc($me['initials']) ?></div>
                <div>
                    <div class="profile-title"><?= esc($me['name']) ?></div>
                    <div class="profile-sub"><?= esc($me['label']) ?> · <?= esc($me['department']) ?></div>
                </div>
                <form method="post" action="<?= site_url('auth/logout') ?>" style="margin-left:12px">
                    <?= csrf_field() ?>
                    <button class="icon-btn" type="submit" aria-label="Se déconnecter" title="Se déconnecter">↪</button>
                </form>
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
