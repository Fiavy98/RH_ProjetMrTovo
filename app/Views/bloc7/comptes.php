<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$activeEmployees = array_values(array_filter($employees, static fn ($employee) => (bool) $employee['actif']));
$selectedEmployeeId = (string) (old('employe_id') ?? ($activeEmployees[0]['id'] ?? ''));
$selectedEmployee = null;
foreach ($activeEmployees as $employee) {
    if ((string) $employee['id'] === $selectedEmployeeId) {
        $selectedEmployee = $employee;
        break;
    }
}
$selectedManagerId = old('manager_id');
if ($selectedManagerId === null) $selectedManagerId = $selectedEmployee['manager_id'] ?? '';
?>
<div class="page-head"><div><p class="eyebrow">Paramétrage RH</p><h1>Comptes salariés</h1><p>Le compte de connexion est rattaché à la fiche employé et au rôle Salarié.</p></div></div>
<?= ui_panel('Salariés', ui_table(['Matricule','Nom','Email','Manager','État'], array_map(static fn ($e) => [
    $e['matricule'],
    $e['prenom'].' '.$e['nom'],
    $e['email'],
    trim(($e['manager_prenom'] ?? '').' '.($e['manager_nom'] ?? '')) ?: '—',
    $e['actif'] ? 'Actif' : 'Inactif',
], $employees))) ?>
<form class="panel" method="post" action="<?= site_url('bloc7/comptes-salaries') ?>">
    <?= csrf_field() ?>
    <div class="panel-head"><h2 class="panel-title">Activer ou réinitialiser un compte</h2></div>
    <div class="panel-body">
        <div class="form-grid">
            <div class="field">
                <label for="employee">Salarié</label>
                <select id="employee" name="employe_id" required>
                    <?php foreach ($activeEmployees as $employee): ?>
                        <option value="<?= (int) $employee['id'] ?>" data-manager-id="<?= esc((string) ($employee['manager_id'] ?? ''), 'attr') ?>" <?= (string) $employee['id'] === $selectedEmployeeId ? 'selected' : '' ?>>
                            <?= esc($employee['matricule'].' · '.$employee['prenom'].' '.$employee['nom'].' — '.$employee['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="manager">Manager</label>
                <select id="manager" name="manager_id">
                    <option value="" <?= (string) $selectedManagerId === '' ? 'selected' : '' ?>>Aucun manager</option>
                    <?php foreach ($managers as $manager): ?>
                        <option value="<?= (int) $manager['id'] ?>" <?= (string) $manager['id'] === (string) $selectedManagerId ? 'selected' : '' ?>>
                            <?= esc($manager['matricule'].' · '.$manager['prenom'].' '.$manager['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Seuls les salariés actifs ayant le rôle Manager sont proposés.</small>
            </div>
            <?= ui_field('mot_de_passe', 'Nouveau mot de passe', 'password', '', ['required' => true, 'hint' => 'Au moins 8 caractères. Il est stocké sous forme chiffrée.']) ?>
        </div>
        <div class="form-actions"><button class="btn primary">Enregistrer le compte</button></div>
    </div>
</form>
<script>
document.getElementById('employee')?.addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    const manager = document.getElementById('manager');
    const managerId = option?.dataset.managerId || '';
    manager.value = [...manager.options].some((item) => item.value === managerId) ? managerId : '';
});
</script>
<?= $this->endSection() ?>
