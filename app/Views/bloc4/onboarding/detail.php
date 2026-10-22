<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><div class="eyebrow">BLOC 4 · ONBOARDING</div><h1>Onboarding de <?= esc($onboarding['prenom'] . ' ' . $onboarding['nom']) ?></h1><p><?= esc($onboarding['matricule']) ?> · Statut global : <?= ui_badge($onboarding['statut']) ?></p></div><div class="action-row"><a class="btn btn-secondary" href="<?= site_url('bloc4/onboardings') ?>">← Liste des onboardings</a></div></div>
<?= ui_notice('Modifiez les tâches ci-dessous puis enregistrez une seule fois. Le statut global de l’onboarding est calculé à partir des six tâches.') ?>
<form method="post" action="<?= site_url('bloc4/onboarding-detail/' . $onboarding['id']) ?>"><?= csrf_field() ?><div class="panel"><div class="panel-head"><div><h2 class="panel-title">Les six tâches d'intégration</h2><p class="panel-sub">Les six lignes sont créées automatiquement au statut « À faire ».</p></div></div><div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Tâche</th><th>Responsable (ID employé)</th><th>Échéance</th><th>Statut</th><th>Date de réalisation</th></tr></thead><tbody>
<?php foreach ($onboarding['taches'] as $task): $taskId = (int) $task['id']; ?>
<tr>
    <td><?= esc($task['libelle']) ?></td>
    <td><select name="responsable_id[<?= $taskId ?>]" aria-label="Responsable de <?= esc($task['libelle'], 'attr') ?>"><option value="">Non attribué</option><?php foreach ($onboarding['responsables'] as $responsable): ?><option value="<?= esc($responsable['id'], 'attr') ?>" <?= (string) ($task['responsable_id'] ?? '') === (string) $responsable['id'] ? 'selected' : '' ?>><?= esc($responsable['matricule'] . ' · ' . $responsable['prenom'] . ' ' . $responsable['nom']) ?></option><?php endforeach; ?></select></td>
    <td><input type="date" name="echeance[<?= $taskId ?>]" value="<?= esc($task['echeance'] ? date('Y-m-d', strtotime($task['echeance'])) : '', 'attr') ?>" aria-label="Échéance de <?= esc($task['libelle'], 'attr') ?>"></td>
    <td><select name="statut[<?= $taskId ?>]" required aria-label="Statut de <?= esc($task['libelle'], 'attr') ?>"><?php foreach (['a_faire', 'en_cours', 'termine'] as $status): ?><option value="<?= esc($status, 'attr') ?>" <?= $task['statut'] === $status ? 'selected' : '' ?>><?= esc(ui_status_label($status)) ?></option><?php endforeach; ?></select></td>
    <td><input type="date" name="date_realisation[<?= $taskId ?>]" value="<?= esc($task['date_realisation'] ? date('Y-m-d', strtotime($task['date_realisation'])) : '', 'attr') ?>" aria-label="Date de réalisation de <?= esc($task['libelle'], 'attr') ?>"></td>
</tr>
<?php endforeach; ?>
</tbody></table></div><div class="form-actions"><button class="btn btn-primary" type="submit">Enregistrer les six tâches</button></div></div></div></form>
<?= $this->endSection() ?>
