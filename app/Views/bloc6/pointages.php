<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 6 · TEMPS ET POINTAGE</div>
        <h1>Entrées, sorties et pointages</h1>
        <p>Suivez les présences réelles en temps réel, identifiez les retards d'arrivée et comptabilisez les heures supplémentaires.</p>
    </div>
    <div class="action-row">
        <button type="button" class="btn btn-primary" onclick="ouvrirModalPointage()">
            ＋ Saisir / Régulariser un pointage
        </button>
    </div>
</div>

<!-- Bandeau KPIs du jour -->
<div class="cards">
    <?php foreach ($stats as [$lbl, $val]): ?>
        <div class="stat">
            <div class="num"><?= esc($val) ?></div>
            <div class="lbl"><?= esc($lbl) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Barre d'outils / filtres -->
<form class="toolbar" method="get" action="<?= site_url('bloc6/pointages') ?>">
    <input type="date" name="date" value="<?= esc($curDate, 'attr') ?>" title="Date du pointage">
    <select name="departement">
        <option value="">Tous les départements</option>
        <?php foreach ($options['departements'] as $dept): ?>
            <option value="<?= esc($dept['id'], 'attr') ?>" <?= ($filters['departement'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                <?= esc($dept['nom_departement']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="statut_pointage">
        <option value="">Tous les statuts</option>
        <option value="retard" <?= ($filters['statut_pointage'] ?? '') === 'retard' ? 'selected' : '' ?>>Salariés en retard</option>
        <option value="heures_sup" <?= ($filters['statut_pointage'] ?? '') === 'heures_sup' ? 'selected' : '' ?>>Heures supplémentaires</option>
        <option value="incomplet" <?= ($filters['statut_pointage'] ?? '') === 'incomplet' ? 'selected' : '' ?>>Sortie manquante</option>
    </select>
    <input type="search" name="employe" value="<?= esc($filters['employe'] ?? '', 'attr') ?>" placeholder="Rechercher par salarié…">
    <button class="btn btn-secondary" type="submit">Filtrer</button>
    <?php if (! empty($filters)): ?>
        <a class="btn btn-white" href="<?= site_url('bloc6/pointages') ?>">Aujourd'hui</a>
    <?php endif; ?>
</form>

<!-- Tableau des pointages -->
<?php
$rows = [];
foreach ($pointages as $p) {
    // 1. Salarié
    $salCell = '<strong>' . esc($p['prenom'] . ' ' . $p['nom']) . '</strong><br><small class="muted">' 
        . esc($p['matricule']) . ' · ' . esc($p['nom_departement'] ?? 'Sans service') . '</small>';

    // 2. Entrée avec statut retard
    $entreeStr = $p['heure_entree'] ? substr((string) $p['heure_entree'], 0, 5) : '—';
    $retardMin = (int) ($p['retard_minutes'] ?? 0);
    if ($retardMin > 0) {
        $entreeBadge = '<span class="badge red">' . $entreeStr . ' (+' . $retardMin . ' min)</span>';
    } else {
        $entreeBadge = '<span class="badge green">' . $entreeStr . '</span>';
    }

    // 3. Sortie
    if (! empty($p['heure_sortie'])) {
        $sortieStr = '<span class="badge gray">' . substr((string) $p['heure_sortie'], 0, 5) . '</span>';
    } else {
        $sortieStr = '<span class="badge orange">En attente</span>';
    }

    // 4. Pause
    $pauseStr = ((int) ($p['pause_minutes'] ?? 0)) . ' min';

    // 5. Temps travaillé
    if ($p['temps_travaille_heures'] !== null) {
        $heuresReelles = (float) $p['temps_travaille_heures'];
        $h = floor($heuresReelles);
        $m = round(($heuresReelles - $h) * 60);
        $dureeStr = '<strong>' . $h . 'h ' . sprintf('%02d', $m) . '</strong>';
    } else {
        $dureeStr = '<span class="muted">—</span>';
    }

    // 6. Heures sup
    $heuresSup = (float) ($p['heures_supplementaires'] ?? 0);
    if ($heuresSup > 0) {
        $mSup = round($heuresSup * 60);
        $supBadge = '<span class="badge blue">+' . $mSup . ' min</span>';
    } else {
        $supBadge = '<span class="muted">—</span>';
    }

    // 7. Statut global
    $statut = $p['statut'] ?? 'VALIDE';
    if ($statut === 'CORRIGE') {
        $statutBadge = '<span class="badge orange" title="' . esc($p['motif_correction'] ?? 'Régularisé', 'attr') . '">Corrigé</span>';
    } elseif ($statut === 'A_CORRIGER') {
        $statutBadge = '<span class="badge red">À vérifier</span>';
    } else {
        $statutBadge = '<span class="badge green">Validé</span>';
    }

    // 8. Action
    $actionBtn = '<button type="button" class="btn btn-sm btn-secondary" onclick="editerPointage(' 
        . (int) $p['employe_id'] . ', \'' . esc($p['date_pointage'], 'js') . '\', \'' 
        . substr((string) $p['heure_entree'], 0, 5) . '\', \'' 
        . substr((string) $p['heure_sortie'], 0, 5) . '\', ' 
        . (int) $p['pause_minutes'] . ', \'' 
        . esc($p['motif_correction'] ?? '', 'js') . '\')">Régulariser</button>';

    $rows[] = [
        ui_raw($salCell),
        ui_date($p['date_pointage']),
        ui_raw($entreeBadge),
        ui_raw($sortieStr),
        $pauseStr,
        ui_raw($dureeStr),
        ui_raw($supBadge),
        ui_raw($statutBadge),
        ui_raw($actionBtn),
    ];
}

echo ui_panel(
    'Journal des présences du ' . date('d/m/Y', strtotime($curDate)),
    ui_table(['Salarié', 'Date', 'Arrivée (Retard)', 'Départ', 'Pause', 'Temps réel', 'Heures sup', 'Statut', 'Action'], $rows, 'Aucun pointage enregistré pour cette date.'),
    'Les retards et heures sup sont calculés en comparant le pointage réel au planning prévu.'
);
?>

<!-- Modale de saisie / régularisation de pointage -->
<div class="modal-backdrop" id="modal-pointage" style="display:none;">
    <div class="modal">
        <header>
            <h3 style="font-size:17px;font-weight:800;" id="pt-modal-titre">Saisie / Régularisation de pointage</h3>
            <button type="button" class="icon-btn" onclick="document.getElementById('modal-pointage').style.display='none'">✕</button>
        </header>
        <form method="post" action="<?= site_url('bloc6/pointages/save') ?>">
            <?= csrf_field() ?>
            <div class="body">
                <div class="form-grid">
                    <div class="field full">
                        <label for="pt-emp">Salarié planifié *</label>
                        <select id="pt-emp" name="employe_id" required>
                            <option value="">Sélectionner un collaborateur planifié…</option>
                        </select>
                        <div id="pt-no-plan-warning" style="display:none; color:var(--red); margin-top:5px; font-size:11px;"></div>
                    </div>
                    <div class="field">
                        <label for="pt-date">Date du pointage *</label>
                        <input type="date" id="pt-date" name="date_pointage" value="<?= esc($curDate, 'attr') ?>" required onchange="actualiserEmployesParDate()">
                    </div>
                    <div class="field">
                        <label for="pt-pause">Pause repas (minutes)</label>
                        <input type="number" id="pt-pause" name="pause_minutes" value="60" min="0" step="5">
                    </div>
                    <div class="field">
                        <label for="pt-entree">Heure d'entrée réelle *</label>
                        <input type="time" id="pt-entree" name="heure_entree" value="08:00" required>
                    </div>
                    <div class="field">
                        <label for="pt-sortie">Heure de sortie réelle</label>
                        <input type="time" id="pt-sortie" name="heure_sortie" value="17:00">
                    </div>
                    <div class="field full">
                        <label for="pt-motif">Motif de régularisation (obligatoire si correction manuelle)</label>
                        <input type="text" id="pt-motif" name="motif_correction" placeholder="ex: Oubli de badgeage, mission extérieure validée…">
                    </div>
                </div>
            </div>
            <footer>
                <button type="button" class="btn btn-white" onclick="document.getElementById('modal-pointage').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary" id="pt-submit-btn">Enregistrer le pointage</button>
            </footer>
        </form>
    </div>
</div>

<script>
const planningsParDate = <?= json_encode($planningsParDate ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function actualiserEmployesParDate() {
    const dateInput = document.getElementById('pt-date');
    const select = document.getElementById('pt-emp');
    const warning = document.getElementById('pt-no-plan-warning');
    const dateVal = dateInput.value;

    select.innerHTML = '';

    const liste = (dateVal && planningsParDate[dateVal]) ? planningsParDate[dateVal] : [];

    if (liste.length === 0) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.disabled = true;
        opt.selected = true;
        opt.textContent = 'Aucun salarié planifié pour cette date';
        select.appendChild(opt);

        const dateParts = dateVal ? dateVal.split('-') : [];
        const dateFr = dateParts.length === 3 ? (dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0]) : dateVal;

        warning.style.display = 'block';
        warning.innerHTML = '⚠ Aucun collaborateur n\'a de planning pour le ' + dateFr + '. <a href="<?= site_url('bloc6/planning?date_ref=') ?>' + dateVal + '" style="font-weight:700;color:var(--red);text-decoration:underline;">Affecter un planning</a> d\'abord.';
    } else {
        warning.style.display = 'none';
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = 'Sélectionner un collaborateur planifié (' + liste.length + ' disponible' + (liste.length > 1 ? 's' : '') + ')…';
        select.appendChild(defaultOpt);

        liste.forEach(function(emp) {
            const opt = document.createElement('option');
            opt.value = emp.id;
            opt.textContent = emp.matricule + ' — ' + emp.prenom + ' ' + emp.nom + ' (' + emp.debut + ' – ' + emp.fin + ')';
            select.appendChild(opt);
        });
    }
}

function ouvrirModalPointage() {
    document.getElementById('pt-modal-titre').innerText = 'Nouveau pointage';
    document.getElementById('pt-motif').value = '';
    actualiserEmployesParDate();
    document.getElementById('modal-pointage').style.display = 'grid';
}

function editerPointage(empId, date, entree, sortie, pause, motif) {
    document.getElementById('pt-modal-titre').innerText = 'Régulariser un pointage';
    document.getElementById('pt-date').value = date;
    actualiserEmployesParDate();

    let empSelect = document.getElementById('pt-emp');
    let opt = empSelect.querySelector('option[value="' + empId + '"]');
    if (!opt) {
        let newOpt = document.createElement('option');
        newOpt.value = empId;
        newOpt.innerText = 'Collaborateur sélectionné';
        empSelect.appendChild(newOpt);
    }
    empSelect.value = empId;
    document.getElementById('pt-entree').value = entree;
    document.getElementById('pt-sortie').value = sortie;
    document.getElementById('pt-pause').value = pause || 60;
    document.getElementById('pt-motif').value = motif || 'Régularisation RH';
    document.getElementById('modal-pointage').style.display = 'grid';
}

document.addEventListener('DOMContentLoaded', function() {
    actualiserEmployesParDate();
});
</script>

<?= $this->endSection() ?>

