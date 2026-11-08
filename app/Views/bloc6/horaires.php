<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 6 · TEMPS ET POINTAGE</div>
        <h1>Horaires et régimes de travail</h1>
        <p>Définissez les régimes horaires types, les plages de travail et les durées de pause applicables aux salariés.</p>
    </div>
    <div class="action-row">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-horaire').style.display='grid'">
            ＋ Nouveau régime horaire
        </button>
    </div>
</div>

<!-- Bandeau KPIs -->
<div class="cards">
    <?php foreach ($stats as [$lbl, $val]): ?>
        <div class="stat">
            <div class="num"><?= esc($val) ?></div>
            <div class="lbl"><?= esc($lbl) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Barre d'outils / filtres -->
<form class="toolbar" method="get" action="<?= site_url('bloc6/horaires') ?>">
    <input type="search" name="recherche" value="<?= esc($filters['recherche'] ?? '', 'attr') ?>" placeholder="Rechercher par code ou libellé…">
    <select name="actif">
        <option value="">Tous les états</option>
        <option value="1" <?= ($filters['actif'] ?? '') === '1' ? 'selected' : '' ?>>Actifs uniquement</option>
        <option value="0" <?= ($filters['actif'] ?? '') === '0' ? 'selected' : '' ?>>Inactifs</option>
    </select>
    <button class="btn btn-secondary" type="submit">Filtrer</button>
    <?php if (! empty($filters)): ?>
        <a class="btn btn-white" href="<?= site_url('bloc6/horaires') ?>">Réinitialiser</a>
    <?php endif; ?>
</form>

<!-- Tableau des régimes -->
<?php
$rows = [];
foreach ($horaires as $h) {
    $isActif = in_array($h['actif'] ?? false, [true, 't', '1', 1, 'true', 'T'], true);
    $statutBadge = $isActif 
        ? '<span class="badge green">Actif</span>' 
        : '<span class="badge gray">Inactif</span>';

    $debut = substr((string) ($h['heure_debut'] ?? '08:00'), 0, 5);
    $fin = substr((string) ($h['heure_fin'] ?? '17:00'), 0, 5);
    $pause = (int) ($h['pause_minutes'] ?? 0) . ' min';
    $dureeJour = number_format((float) ($h['heures_jour'] ?? 0), 2) . ' h / jour';
    $dureeHebdo = number_format((float) ($h['heures_hebdo'] ?? 0), 2) . ' h / sem.';

    $libelleHtml = '<strong>' . esc($h['libelle']) . '</strong>';
    if (! empty($h['description'])) {
        $libelleHtml .= '<br><small class="muted">' . esc($h['description']) . '</small>';
    }

    $rows[] = [
        esc($h['code']),
        ui_raw($libelleHtml),
        "$debut – $fin",
        $pause,
        $dureeJour,
        $dureeHebdo,
        ui_raw($statutBadge),
    ];
}
echo ui_panel(
    'Catalogue des régimes de travail',
    ui_table(['Code', 'Libellé', 'Plage horaire', 'Pause', 'Durée jour', 'Durée hebdo', 'Statut'], $rows, 'Aucun régime horaire enregistré.'),
    'Les heures calculées servent de base au contrôle des retards et heures supplémentaires.'
);
?>

<!-- Modale d'ajout d'un horaire -->
<div class="modal-backdrop" id="modal-horaire" style="display:none;">
    <div class="modal">
        <header>
            <h3 style="font-size:17px;font-weight:800;">Nouveau régime horaire</h3>
            <button type="button" class="icon-btn" onclick="document.getElementById('modal-horaire').style.display='none'">✕</button>
        </header>
        <form method="post" action="<?= site_url('bloc6/horaires/save') ?>">
            <?= csrf_field() ?>
            <div class="body">
                <div class="form-grid">
                    <div class="field">
                        <label for="h-code">Code du régime *</label>
                        <input type="text" id="h-code" name="code" placeholder="ex: STANDARD_40H" required style="text-transform:uppercase;">
                    </div>
                    <div class="field">
                        <label for="h-libelle">Libellé complet *</label>
                        <input type="text" id="h-libelle" name="libelle" placeholder="ex: Standard 08h00 - 17h00" required>
                    </div>
                    <div class="field">
                        <label for="h-debut">Heure d'arrivée prévue *</label>
                        <input type="time" id="h-debut" name="heure_debut" value="08:00" required>
                    </div>
                    <div class="field">
                        <label for="h-fin">Heure de départ prévue *</label>
                        <input type="time" id="h-fin" name="heure_fin" value="17:00" required>
                    </div>
                    <div class="field">
                        <label for="h-pause">Pause repas (en minutes) *</label>
                        <input type="number" id="h-pause" name="pause_minutes" value="60" min="0" step="5" required>
                    </div>
                    <div class="field">
                        <label for="h-actif">Statut</label>
                        <select id="h-actif" name="actif">
                            <option value="1">Actif (disponible pour planning)</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                    <div class="field full">
                        <label for="h-desc">Description / Particularités</label>
                        <textarea id="h-desc" name="description" rows="2" placeholder="ex: Applicable aux équipes administratives du lundi au vendredi."></textarea>
                    </div>
                </div>
            </div>
            <footer>
                <button type="button" class="btn btn-white" onclick="document.getElementById('modal-horaire').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer le régime</button>
            </footer>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
