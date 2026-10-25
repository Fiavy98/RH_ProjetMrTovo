<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= esc($pageTitle) ?> | NovaRH</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/sirh.css') ?>">
</head>
<body>
<main class="workspace" style="max-width:760px;margin:8vh auto">
    <section class="panel">
        <div class="panel-head">
            <div>
                <div class="eyebrow">NovaRH · Démonstration</div>
                <h1 class="panel-title">Choisir un utilisateur</h1>
                <p class="panel-sub">Sélectionnez le profil à utiliser pour ouvrir la session.</p>
            </div>
        </div>
        <div class="panel-body">
            <?php if ($error): ?>
                <?= ui_notice($error, 'danger') ?>
            <?php endif; ?>
            <?php if ($employees === []): ?>
                <?= ui_notice('Aucun utilisateur actif avec un rôle n’est disponible dans la base.', 'danger') ?>
            <?php else: ?>
                <form method="post" action="<?= site_url('auth/select') ?>">
                    <?= csrf_field() ?>
                    <label for="employee_id">Utilisateur</label>
                    <select id="employee_id" name="employee_id" required>
                        <option value="">Sélectionner un profil</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= esc((string) $employee->id, 'attr') ?>">
                                <?= esc(trim($employee->prenom . ' ' . $employee->nom) . ' — ' . $employee->nom_role . ' · ' . $employee->nom_departement) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn primary" type="submit" style="margin-top:20px">Entrer dans l’application</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
