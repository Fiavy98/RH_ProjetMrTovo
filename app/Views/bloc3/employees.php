<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">Bloc 3 / Sélection et embauche</div>
        <h1>Dossiers salariés</h1>
        <p>
            Créez et suivez les dossiers salariés après acceptation du candidat et approbation
            de la Direction.
        </p>
    </div>
</div>

<?php
$candidateDetailsJson = json_encode($candidateDetails ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<?= ui_panel(
    'Créer un dossier salarié',
    '<form method="post" action="' . site_url('bloc3/employees') . '">' . csrf_field() .
    '<div class="form-grid">'
    . ui_field('candidate', 'Nom', 'select', '', ['required' => true, 'options' => $candidateOptions])
    . ui_field('nom', 'Nom complet', 'text', '', ['required' => true, 'readonly' => true])
    . ui_field('prenom', 'Prénom', 'text', '', ['required' => true, 'readonly' => true])
    . ui_field('email', 'Adresse e-mail', 'email', '', ['required' => true, 'readonly' => true])
    . ui_field('departement_id', 'Département', 'select', '', ['required' => true, 'options' => $departmentOptions])
    . ui_field('matricule', 'Matricule', 'text', '', ['required' => true, 'readonly' => true])
    . ui_field('date_embauche', 'Date d’embauche', 'date', date('Y-m-d'), ['required' => true])
    . '</div>'
    . '<div class="form-actions"><button class="btn btn-primary" type="submit">Créer le dossier salarié</button></div></form>',
    'Le dossier salarié est créé après validation du candidat et de la Direction.'
) ?>

<script>
    const candidateDetails = <?php echo $candidateDetailsJson; ?>;
    const candidateSelect = document.getElementById('f-candidate');
    const nomField = document.getElementById('f-nom');
    const prenomField = document.getElementById('f-prenom');
    const emailField = document.getElementById('f-email');

    function updateCandidateDetails() {
        const candidateId = candidateSelect?.value || '';
        const candidate = candidateDetails[candidateId] || null;

        if (!candidate) {
            if (nomField) nomField.value = '';
            if (prenomField) prenomField.value = '';
            if (emailField) emailField.value = '';
            return;
        }

        if (nomField) nomField.value = candidate.nom || '';
        if (prenomField) prenomField.value = candidate.prenom || '';
        if (emailField) emailField.value = candidate.email || '';
    }

    if (candidateSelect) {
        candidateSelect.addEventListener('change', updateCandidateDetails);
        updateCandidateDetails();
    }
</script>

<?= ui_panel(
    'Salariés enregistrés',
    ui_table(
        [
            'Matricule',
            'Nom',
            'Prénom',
            'Poste',
            'Département',
            'Date embauche',
            'Statut',
        ],
        array_map(
            static fn ($employee) => [
                $employee['matricule'],
                $employee['nom'],
                $employee['prenom'],
                $employee['poste'],
                $employee['departement'],
                $employee['date_embauche'],
                $employee['statut'],
            ],
            $employees
        )
    ),
    'Vue RH de la liste des employés validés dans le Bloc 3.'
) ?>

<?= $this->endSection() ?>
