<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
    $candidateName = trim((string) ($selected['candidat_prenom'] ?? '') . ' ' . (string) ($selected['candidat_nom'] ?? ''));
    $candidateName = trim($candidateName) !== '' ? $candidateName : '—';
    $offreTitre = (string) ($selected['poste'] ?? '');
    $budgetMin = isset($budget_min) ? (float) $budget_min : 0;
    $budgetMax = isset($budget_max) ? (float) $budget_max : 0;
    $salaryDefault = isset($salaire_default) ? (float) $salaire_default : 0;
    $salaryRangeText = ($budgetMin > 0 || $budgetMax > 0)
        ? 'Le salaire proposé doit être compris entre ' . number_format($budgetMin, 0, ',', ' ') . ' Ar et ' . number_format($budgetMax, 0, ',', ' ') . ' Ar.'
        : 'Renseignez un salaire compatible avec le budget de la demande.';
?>

<div class="page-head">
    <div>
        <div class="eyebrow">Bloc 3 / Sélection et embauche</div>
        <h1>Proposition d’embauche</h1>
        <p>
            Préparez la proposition de contrat en fonction du candidat retenu,
            du poste concerné et de la date d’entrée prévue.
        </p>
    </div>
</div>

<?= ui_panel(
    'Proposition de contrat',
    '<form method="post" action="' . site_url('bloc3/proposal-form') . '">' . csrf_field() .
    '<input type="hidden" name="candidature_id" value="' . esc((string) ($selected['candidature_id'] ?? 0), 'attr') . '">' .
    '<input type="hidden" name="offre_emploi_id" value="' . esc((string) ($selected['offre_emploi_id'] ?? 0), 'attr') . '">' .
    '<div class="form-grid">'
    . ui_field('candidate', 'Candidat retenu', 'text', $candidateName, ['required' => true, 'readonly' => true])
    . ui_field('position', 'Poste proposé', 'text', $offreTitre, ['required' => true, 'readonly' => true])
    . ui_field('contract_type', 'Type de contrat', 'select', $selected['type_contrat'] ?? 'CE', ['required' => true, 'options' => ['CDD' => 'CDD', 'CDI' => 'CDI', 'CE' => 'CE']])
    . ui_field('salary', 'Salaire proposé (Ar)', 'number', (string) $salaryDefault, ['required' => true, 'min' => (string) $budgetMin, 'max' => (string) $budgetMax, 'step' => '1000', 'hint' => $salaryRangeText])
    . ui_field('start', 'Date de début', 'date', $selected['date_debut'] ?? date('Y-m-d'), ['required' => true])
    . ui_field('comment', 'Commentaires / conditions', 'textarea', 'Période d’essai de 3 mois, poste compatible avec la demande', ['full' => true])
    . '</div>'
    . '<div class="form-actions"><button class="btn btn-primary" type="submit">Valider la proposition</button></div></form>',
    'La proposition doit être envoyée au candidat avant la validation de la Direction.'
) ?>

<?= ui_panel(
    'Historique des propositions',
    ui_table(
        [
            'ID',
            'Candidat',
            'Poste',
            'Type',
            'Salaire',
            'Date de début',
            'Statut',
        ],
        array_map(
            static fn ($item) => [
                $item['id'],
                $item['nom'] . ' ' . $item['prenom'],
                $item['poste'],
                $item['type_contrat'],
                $item['salaire'] . ' Ar',
                $item['date_debut'],
                $item['statut'],
            ],
            $candidatures
        )
    ),
    'Suivi RH des propositions préparées pour les candidats retenus.'
) ?>

<?= $this->endSection() ?>
