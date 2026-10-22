<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <div class="eyebrow">Bloc 3 / Sélection et embauche</div>
        <h1>Candidats retenus</h1>
        <p>
            Consultez les candidats retenus ainsi que les offres d'emploi
            auxquelles ils ont postulé.
        </p>
    </div>
</div>

<?= ui_panel(
    'Liste des candidatures retenues',
    ui_table(
        [
            'ID candidature',
            'Nom du candidat',
            'Prénom',
            'Offre d’emploi',
            'Lettre de motivation',
            'Statut',
            'Action'
        ],
        array_map(
            static function ($candidature) {
                $link = site_url('bloc3/proposal-form?candidature_id=' . (int) $candidature['id']);
                $linkLabel = '<a href="' . esc($link, 'attr') . '" title="Voir la proposition pour cette candidature"># ' . (int) $candidature['id'] . '</a>';
                $button = '<a class="btn btn-primary" href="' . esc($link, 'attr') . '">Voir proposition</a>';

                return [
                    ui_raw($linkLabel),
                    $candidature['nom_candidat'],
                    $candidature['prenom_candidat'],
                    $candidature['titre_offre'],
                    $candidature['lettre_motivation'] ?? '',
                    $candidature['statut'],
                    ui_raw($button),
                ];
            },
            $candidatures
        )
    ),
    'Candidatures dont le statut est RETENUE.'
) ?>

<?= $this->endSection() ?>