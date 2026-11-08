<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 6 · TEMPS ET POINTAGE</div>
        <h1>Planning de travail hebdomadaire</h1>
        <p>Consultez et planifiez les régimes horaires et les jours de travail par salarié pour la période sélectionnée.</p>
    </div>
    <div class="action-row">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-planning').style.display='grid'">
            ＋ Affecter un planning
        </button>
    </div>
</div>

<!-- Navigation semaine & filtres -->
<div style="background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:16px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
    <div class="action-row">
        <a class="btn btn-white btn-sm" href="<?= site_url('bloc6/planning?date_ref=' . $prevWeek . '&' . http_build_query(array_diff_key($filters, ['date_ref' => '']))) ?>">
            ◀ Semaine précédente
        </a>
        <a class="btn btn-secondary btn-sm" href="<?= site_url('bloc6/planning?date_ref=' . date('Y-m-d')) ?>">
            Semaine courante
        </a>
        <a class="btn btn-white btn-sm" href="<?= site_url('bloc6/planning?date_ref=' . $nextWeek . '&' . http_build_query(array_diff_key($filters, ['date_ref' => '']))) ?>">
            Semaine suivante ▶
        </a>
        <span style="font-weight:700;font-size:13px;color:var(--ink);margin-left:8px;">
            Semaine du <?= date('d/m/Y', strtotime($lundi)) ?> au <?= date('d/m/Y', strtotime($dimanche)) ?>
        </span>
    </div>

    <form class="toolbar" method="get" action="<?= site_url('bloc6/planning') ?>" style="margin-bottom:0;">
        <input type="hidden" name="date_ref" value="<?= esc($dateRef, 'attr') ?>">
        <select name="departement">
            <option value="">Tous les départements</option>
            <?php foreach ($options['departements'] as $dept): ?>
                <option value="<?= esc($dept['id'], 'attr') ?>" <?= ($filters['departement'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                    <?= esc($dept['nom_departement']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="search" name="employe" value="<?= esc($filters['employe'] ?? '', 'attr') ?>" placeholder="Matricule ou nom…">
        <button class="btn btn-secondary" type="submit">Filtrer</button>
    </form>
</div>

<!-- Grille de planning -->
<section class="panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Grille des présences prévues</h2>
            <p class="panel-sub">Horaires de début, de fin et volume d'heures journalier par collaborateur.</p>
        </div>
    </div>
    <div class="panel-body" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="min-width:180px;">Salarié</th>
                        <?php foreach ($datesSemaine as $d): ?>
                            <th style="text-align:center; min-width:115px; <?= $d['est_weekend'] ? 'background:#f8fafd;' : '' ?>">
                                <div><?= esc($d['jour_nom']) ?></div>
                                <small class="muted" style="font-weight:normal;"><?= esc($d['jour_num']) ?></small>
                            </th>
                        <?php endforeach; ?>
                        <th style="text-align:center; min-width:110px;">Total Hebdo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grille)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty">
                                    <span class="big">⌕</span>
                                    Aucun salarié ne correspond aux critères de filtre.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grille as $row): 
                            $emp = $row['employe'];
                        ?>
                            <tr>
                                <td>
                                    <strong><?= esc($emp['prenom'] . ' ' . $emp['nom']) ?></strong>
                                    <div class="small muted"><?= esc($emp['matricule']) ?> · <?= esc($emp['nom_departement'] ?? 'Général') ?></div>
                                </td>
                                <?php foreach ($row['jours'] as $j): ?>
                                    <td style="text-align:center; font-size:12px; <?= $j['est_repos'] ? 'background:#fbfcfe;' : '' ?>">
                                        <?php if ($j['est_repos']): ?>
                                            <span class="badge gray">Repos</span>
                                        <?php elseif (! empty($j['planifie']) && ! empty($j['heure_debut'])): ?>
                                            <div style="font-weight:700; color:var(--ink);">
                                                <?= esc($j['heure_debut']) ?> – <?= esc($j['heure_fin']) ?>
                                            </div>
                                            <div class="small muted" style="margin-top:2px;">
                                                <?= number_format((float) $j['heures'], 1) ?> h
                                            </div>
                                        <?php else: ?>
                                            <span class="muted" style="color:#a8b6cb;">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td style="text-align:center; font-weight:800; font-size:13px; color:var(--blue);">
                                    <?= number_format((float) $row['total_heures'], 1) ?> h
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modale d'affectation de planning -->
<div class="modal-backdrop" id="modal-planning" style="display:none;">
    <div class="modal">
        <header>
            <h3 style="font-size:17px;font-weight:800;">Affecter un planning à un collaborateur</h3>
            <button type="button" class="icon-btn" onclick="document.getElementById('modal-planning').style.display='none'">✕</button>
        </header>
        <form method="post" action="<?= site_url('bloc6/planning/save') ?>">
            <?= csrf_field() ?>
            <div class="body">
                <div class="form-grid">
                    <div class="field full">
                        <label for="p-emp">Salarié *</label>
                        <select id="p-emp" name="employe_id" required>
                            <option value="">Sélectionner un collaborateur…</option>
                            <?php foreach ($options['employes'] as $emp): ?>
                                <option value="<?= esc($emp['id'], 'attr') ?>">
                                    <?= esc($emp['matricule'] . ' — ' . $emp['prenom'] . ' ' . $emp['nom'] . ' (' . ($emp['nom_departement'] ?? 'Sans service') . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field full">
                        <label for="p-hor">Régime horaire à appliquer *</label>
                        <select id="p-hor" name="horaire_id" required>
                            <option value="">Sélectionner un horaire type…</option>
                            <?php foreach ($options['horaires'] as $hor): ?>
                                <option value="<?= esc($hor['id'], 'attr') ?>">
                                    <?= esc($hor['code'] . ' · ' . $hor['libelle'] . ' (' . substr($hor['heure_debut'], 0, 5) . ' - ' . substr($hor['heure_fin'], 0, 5) . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="p-debut">Date de début *</label>
                        <input type="date" id="p-debut" name="date_debut" value="<?= esc($lundi, 'attr') ?>" required>
                    </div>
                    <div class="field">
                        <label for="p-fin">Date de fin *</label>
                        <input type="date" id="p-fin" name="date_fin" value="<?= esc($dimanche, 'attr') ?>" required>
                    </div>
                    <div class="field full">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="exclure_weekend" value="1" checked style="width:auto;margin:0;">
                            <span>Exclure le week-end (samedi et dimanche configurés en jour de repos)</span>
                        </label>
                    </div>
                </div>
            </div>
            <footer>
                <button type="button" class="btn btn-white" onclick="document.getElementById('modal-planning').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Valider la planification</button>
            </footer>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

