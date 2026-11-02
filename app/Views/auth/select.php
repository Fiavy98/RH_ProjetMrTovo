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
<div class="login-screen">
    <section class="login-hero">
        <div>
            <div class="brand-mark">N</div>
            <div style="margin-top:60px">
                <span class="demo-tag">SIRH · VERSION DÉMONSTRATION</span>
                <h1 style="margin-top:18px">Les ressources humaines, avec clarté.</h1>
                <p>Du besoin initial à l'intégration du salarié, explorez une expérience de gestion RH fluide, organisée et cohérente.</p>
            </div>
        </div>
        <div class="login-badges">
            <span class="demo-tag">04 blocs connectés</span>
            <span class="demo-tag">06 profils métier</span>
            <span class="demo-tag">Frontend + Backend</span>
        </div>
    </section>

    <section class="login-side">
        <div class="login-card">
            <div class="eyebrow">Bienvenue sur NovaRH</div>
            <h2>Connexion de démonstration</h2>
            <p class="muted" style="margin-top:9px">Aucun compte réel n'est utilisé. Choisissez un rôle pour tester les écrans.</p>

            <?php if ($error): ?>
                <?= ui_notice($error, 'danger') ?>
            <?php endif; ?>

            <?php if (! $hasProfiles): ?>
                <?= ui_notice('Aucun utilisateur actif avec un rôle compatible n’est disponible dans la base.', 'danger') ?>
            <?php endif; ?>

            <form method="post" action="<?= site_url('auth/select') ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="login-role">Profil de démonstration *</label>
                    <select name="role" id="login-role" required>
                        <?php foreach ($profiles as $key => $profile): ?>
                            <option value="<?= esc($key, 'attr') ?>" <?= $profile['available'] ? '' : 'disabled' ?>>
                                <?= esc($profile['label'] . ' — ' . $profile['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="login-user">Identifiant de démonstration *</label>
                    <input id="login-user" name="login" value="demo" required autocomplete="off">
                </div>

                <div class="field">
                    <label for="login-pass">Mot de passe de démonstration *</label>
                    <div class="field-inline">
                        <input id="login-pass" type="password" name="password" value="demo" required autocomplete="off">
                        <button class="icon-btn" type="button" data-toggle-password aria-label="Afficher le mot de passe">◉</button>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit" <?= $hasProfiles ? '' : 'disabled' ?>>Accéder à l'espace RH →</button>
            </form>

            <div class="notice" style="margin-top:22px">Simulation visuelle uniquement. Les rôles ne constituent pas une sécurité serveur. Vos changements restent locaux à ce navigateur.</div>
            <p class="small muted">Utiliser <b>demo / demo</b> uniquement pour ce prototype.</p>
        </div>
    </section>
</div>
<script>
document.querySelector('[data-toggle-password]')?.addEventListener('click', function () {
    const password = document.getElementById('login-pass');
    const visible = password.type === 'password';
    password.type = visible ? 'text' : 'password';
    this.setAttribute('aria-label', visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
});
</script>
</body>
</html>
