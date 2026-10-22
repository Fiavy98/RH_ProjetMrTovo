<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">Bloc 3 / Sélection et embauche</div>
        <h1>Validation de la Direction</h1>
        <p>
            Après l’acceptation du candidat, la Direction décide d’approuver ou de refuser
            l’embauche définitive.
        </p>
    </div>
</div>

<?php
$selectedProposal = $selectedProposal ?? null;
$selectedProposalId = (string) ($selectedProposalId ?? '');
$selectedCandidate = $selectedProposal['candidat'] ?? '—';
$selectedPoste = $selectedProposal['poste'] ?? '—';
$selectedContrat = $selectedProposal['contrat'] ?? '—';
$selectedSalaire = isset($selectedProposal['salaire']) ? number_format((float) $selectedProposal['salaire'], 0, ',', ' ') . ' Ar' : '—';
$selectedDateDebut = $selectedProposal['date_debut'] ?? '—';
$proposalDetailsJson = json_encode($proposalDetails ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<?= ui_panel(
    'Décision Direction à traiter',
    '<form method="post" action="' . site_url('bloc3/hire-validations') . '">' . csrf_field() .
    '<div class="form-grid">'
    . ui_field('candidate', 'Candidat', 'select', $selectedProposalId, ['required' => true, 'options' => $candidateOptions])
    . '<div class="field full"><label>Informations de la proposition</label><div id="proposal-details" class="panel-soft" style="padding:12px 14px; border:1px solid #dfe3e8; border-radius:8px; background:#f8fafc;">'
        . '<div><strong>Candidat :</strong> <span id="detail-candidat">' . esc($selectedCandidate) . '</span></div>'
        . '<div><strong>Poste :</strong> <span id="detail-poste">' . esc($selectedPoste) . '</span></div>'
        . '<div><strong>Type de contrat :</strong> <span id="detail-contrat">' . esc($selectedContrat) . '</span></div>'
        . '<div><strong>Salaire proposé :</strong> <span id="detail-salaire">' . esc($selectedSalaire) . '</span></div>'
        . '<div><strong>Date de début :</strong> <span id="detail-date">' . esc((string) $selectedDateDebut) . '</span></div>'
        . '</div></div>'
    . ui_field('decision', 'Décision Direction', 'select', 'Accepter', ['required' => true, 'options' => ['Accepter' => 'Accepter', 'Refuser' => 'Refuser']])
    . ui_field('comment', 'Commentaire', 'textarea', '', ['full' => true, 'placeholder' => 'Commentaire de la direction'])
    . '</div>'
    . '<div class="form-actions"><button class="btn btn-primary" type="submit">Confirmer la décision</button></div></form>',
    'Cette étape suit l’acceptation du candidat et précède la création du dossier salarié.'
) ?>

<script>
    const proposalDetails = <?php echo $proposalDetailsJson; ?>;
    const candidateSelect = document.getElementById('f-candidate');

    function updateProposalDetails() {
        const proposalId = candidateSelect?.value || '';
        const proposal = proposalDetails[proposalId] || null;

        if (!proposal) {
            document.getElementById('detail-candidat').textContent = '—';
            document.getElementById('detail-poste').textContent = '—';
            document.getElementById('detail-contrat').textContent = '—';
            document.getElementById('detail-salaire').textContent = '—';
            document.getElementById('detail-date').textContent = '—';
            return;
        }

        document.getElementById('detail-candidat').textContent = proposal.candidat || '—';
        document.getElementById('detail-poste').textContent = proposal.poste || '—';
        document.getElementById('detail-contrat').textContent = proposal.contrat || '—';
        document.getElementById('detail-salaire').textContent = proposal.salaire || '—';
        document.getElementById('detail-date').textContent = proposal.date_debut || '—';
    }

    if (candidateSelect) {
        candidateSelect.addEventListener('change', updateProposalDetails);
        updateProposalDetails();
    }
</script>

<?= ui_panel(
    'Demandes en attente',
    ui_table(
        [
            'ID',
            'Candidat',
            'Poste',
            'Salaire',
            'Contrat',
            'Date départ',
            'Statut',
        ],
        array_map(
            static fn ($item) => [
                $item['id'],
                $item['candidat'],
                $item['poste'],
                $item['salaire'],
                $item['contrat'],
                $item['date_debut'],
                $item['statut'],
            ],
            $validations
        )
    ),
    'Les dossiers considérés ont reçu une réponse favorable du candidat.'
) ?>

<?= $this->endSection() ?>
