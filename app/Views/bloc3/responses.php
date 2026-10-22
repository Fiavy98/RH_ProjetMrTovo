<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">Bloc 3 / Sélection et embauche</div>
        <h1>Réponses candidats</h1>
        <p>
            La RH enregistre la réponse du candidat à la proposition d’embauche,
            puis la Direction valide ou refuse la candidature.
        </p>
    </div>
</div>

<?= ui_panel(
    'Réponse à la proposition',
    '<form method="post" action="' . site_url('bloc3/responses') . '">' . csrf_field() .
    '<div class="form-grid">'
    . ui_field('candidate', 'Candidat', 'select', '', ['required' => true, 'options' => $candidateOptions])
    . ui_field('decision', 'Décision reçue', 'select', 'Acceptée', ['required' => true, 'options' => ['Acceptée' => 'Acceptée', 'Refusée' => 'Refusée']])
    . ui_field('date_response', 'Date de réponse', 'date', date('Y-m-d'), ['required' => true])
    . ui_field('comment', 'Commentaire RH', 'textarea', '', ['full' => true])
    . '</div>'
    . '<div class="form-actions"><button class="btn btn-primary" type="submit">Enregistrer la réponse</button></div></form>',
    'Le statut de la proposition dépend du retour du candidat.'
) ?>

<?= ui_panel(
    'Historique des réponses',
    ui_table(
        [
            'ID',
            'Candidat',
            'Poste',
            'Type',
            'Date réponse',
            'Décision',
            'Statut',
        ],
        array_map(
            static fn ($item) => [
                $item['id'],
                $item['candidat'],
                $item['poste'],
                $item['type_contrat'],
                $item['date_reponse'],
                $item['decision'],
                $item['statut'],
            ],
            $responses
        )
    ),
    'Les réponses accepté/refus orientent le workflow de validation Direction.'
) ?>

<?= $this->endSection() ?>
