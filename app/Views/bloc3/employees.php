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
    'Candidats éligibles',
    empty($eligibleCandidates)
        ? '<p>Aucun candidat éligible pour le moment.</p>'
        : ui_table(
            [
                'Nom',
                'Email',
                'Téléphone',
                'Poste',
                'Contrat',
                'Salaire',
                'Date de début',
                'Action',
            ],
            array_map(
                static function (array $candidate): array {
                    return [
                        ($candidate['prenom'] ?? '') . ' ' . ($candidate['nom'] ?? ''),
                        $candidate['email'] ?? '—',
                        $candidate['telephone'] ?? '—',
                        $candidate['poste'] ?? '—',
                        $candidate['type_contrat'] ?? '—',
                        ! empty($candidate['salaire']) ? number_format((float) $candidate['salaire'], 0, ',', ' ') . ' Ar' : '—',
                        $candidate['date_debut'] ?? '—',
                        ui_raw('<button type="button" class="btn btn-primary select-candidate" data-candidate-id="' . (int) ($candidate['candidate_id'] ?? 0) . '">Sélectionner</button>'),
                    ];
                },
                $eligibleCandidates
            )
        ),
    'Les candidats affichés ont une proposition acceptée et validée par la Direction.'
) ?>

<?= ui_panel(
    'Créer un dossier salarié',
    '<div id="employee-form-wrapper" style="display:none;">'
    . '<form id="employee-form" method="post" action="' . site_url('bloc3/employees') . '" enctype="multipart/form-data">' . csrf_field() .
    '<input type="hidden" name="candidate" id="candidate-id" value="">'
    . '<div class="form-grid">'
    . ui_field('nom', 'Nom complet', 'text', '', ['required' => true, 'readonly' => true])
    . ui_field('prenom', 'Prénom', 'text', '', ['required' => true, 'readonly' => true])
    . ui_field('email', 'Adresse e-mail', 'email', '', ['required' => true, 'readonly' => true])
    . ui_field('telephone', 'Téléphone', 'tel', '', [])
    . ui_field('departement_id', 'Département', 'select', '', ['required' => true, 'options' => $departmentOptions ?? ['' => 'Choisir un département']])
    . ui_field('date_embauche', 'Date d’embauche', 'date', date('Y-m-d'), ['required' => true])
    . ui_field('date_naissance', 'Date de naissance', 'date', '', [])
    . ui_field('numero_cni', 'Numéro CNI', 'text', '', [])
    . ui_field('date_delivrance_cni', 'Date de délivrance CNI', 'date', '', [])
    . ui_field('lieu_delivrance_cni', 'Lieu de délivrance CNI', 'text', '', [])
    . ui_field('adresse', 'Adresse', 'text', '', [])
    . ui_field('situation_familiale', 'Situation familiale', 'select', '', ['options' => [
        '' => 'Choisir une option',
        'Célibataire' => 'Célibataire',
        'Marié' => 'Marié',
        'Divorcé' => 'Divorcé',
        'Veuf' => 'Veuf',
        'Concubinage' => 'Concubinage',
    ]])
    . ui_field('photo', 'Photo du salarié', 'file', '', ['accept' => 'image/png,image/jpeg,image/webp'])
    . '</div>'
    . '<div class="form-actions"><button class="btn btn-primary" type="submit">Créer le dossier salarié</button></div></form>'
    . '</div>',
    'Le RH complète les informations manquantes avant la création du dossier salarié.'
) ?>

<script>
    const candidateDetails = <?php echo $candidateDetailsJson; ?>;
    const candidateInput = document.getElementById('candidate-id');
    const employeeFormWrapper = document.getElementById('employee-form-wrapper');
    const employeeForm = document.getElementById('employee-form');
    const nomField = document.getElementById('f-nom');
    const prenomField = document.getElementById('f-prenom');
    const emailField = document.getElementById('f-email');
    const telephoneField = document.getElementById('f-telephone');

    function showEmployeeForm() {
        if (employeeFormWrapper) {
            employeeFormWrapper.style.display = 'block';
        }

        if (employeeForm) {
            employeeForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function updateCandidateDetails() {
        const candidateId = candidateInput?.value || '';
        const candidate = candidateDetails[candidateId] || null;

        if (!candidate) {
            if (nomField) nomField.value = '';
            if (prenomField) prenomField.value = '';
            if (emailField) emailField.value = '';
            if (telephoneField) telephoneField.value = '';
            return;
        }

        if (nomField) nomField.value = candidate.nom || '';
        if (prenomField) prenomField.value = candidate.prenom || '';
        if (emailField) emailField.value = candidate.email || '';
        if (telephoneField) telephoneField.value = candidate.telephone || '';
    }

    document.querySelectorAll('.select-candidate').forEach(function (button) {
        button.addEventListener('click', function () {
            const selectedId = button.dataset.candidateId || '';
            if (!selectedId) {
                return;
            }

            if (candidateInput) {
                candidateInput.value = selectedId;
            }

            updateCandidateDetails();
            showEmployeeForm();
        });
    });
</script>

<?= $this->endSection() ?>
