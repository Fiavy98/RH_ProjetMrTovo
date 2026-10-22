<?php

namespace App\Services;

/** Données et opérations de démonstration partagées par les écrans des blocs. */
abstract class BlocPageService
{
    /** @return array<string, array<string, mixed>> */
    abstract public function screens(): array;

    /** Construit les métadonnées des pages à partir des entrées du menu du prototype. */
    protected function defineScreens(array $titles, string $area): array
    {
        $screens = [];
        foreach ($titles as $slug => $title) {
            $isForm = str_contains($slug, 'form') || str_contains($slug, 'new') || str_contains($slug, 'settings')
                || in_array($slug, ['hierarchy', 'rh-review', 'finance-review', 'direction-review', 'preselection', 'decisions', 'responses', 'hire-validations', 'assignment-b3', 'assignment-b4', 'handoff', 'onboarding-detail'], true);
            $kind = $slug === 'dashboard' ? 'dashboard' : ($isForm ? 'form' : 'list');
            $screens[$slug] = [
                'title' => $title,
                'description' => $area . ' · ' . $title . '. Écran de base à compléter avec les règles métier du bloc.',
                'kind' => $kind,
                'columns' => [
                    ['name' => 'reference', 'label' => 'Référence'],
                    ['name' => 'subject', 'label' => 'Élément'],
                    ['name' => 'status', 'label' => 'Statut'],
                    ['name' => 'date', 'label' => 'Date'],
                ],
                'sample' => [
                    ['Réf. 001', $title . ' — à traiter', 'En cours', date('d/m/Y')],
                    ['Réf. 002', $title . ' — exemple', 'En attente', date('d/m/Y', strtotime('-2 days'))],
                ],
                'fields' => $isForm ? $this->fieldsFor($slug) : [],
            ];
        }
        return $screens;
    }

    private function fieldsFor(string $slug): array
    {
        $text = static fn (string $name, string $label, bool $required = false, string $type = 'text') => ['name' => $name, 'label' => $label, 'type' => $type, 'required' => $required];
        $select = static fn (string $name, string $label, array $options) => ['name' => $name, 'label' => $label, 'type' => 'select', 'required' => true, 'options' => $options];
        return match ($slug) {
            'demande-form' => [$text('subject', 'Intitulé du poste', true), $text('department', 'Département', true), $text('motif', 'Motif du besoin', true, 'textarea'), $text('budget', 'Budget prévisionnel (Ar)', false, 'number')],
            'offer-form' => [$text('subject', 'Titre de l’offre', true), $text('description', 'Description', true, 'textarea'), $text('published', 'Date de publication', false, 'date'), $text('closing', 'Date de clôture', false, 'date')],
            'candidate-form' => [$text('last_name', 'Nom', true), $text('first_name', 'Prénom', true), $text('email', 'Adresse e-mail', true, 'email'), $text('phone', 'Téléphone')],
            'application-form' => [$text('candidate', 'Candidat', true), $text('offer', 'Offre d’emploi', true), $text('cover_letter', 'Lettre de motivation', false, 'textarea')],
            'interview-form' => [$text('application', 'Candidature', true), $text('date', 'Date et heure', true, 'datetime-local'), $text('location', 'Lieu', true), $text('notes', 'Notes', false, 'textarea')],
            'evaluation-form' => [$text('score', 'Note (sur 20)', true, 'number'), $select('decision', 'Avis', ['Favorable', 'À revoir', 'Défavorable']), $text('comment', 'Commentaire', true, 'textarea')],
            'proposal-form' => [$text('candidate', 'Candidat retenu', true), $select('contract_type', 'Type de contrat', ['CE', 'CDD', 'CDI']), $text('salary', 'Salaire proposé (Ar)', true, 'number'), $text('start', 'Date de début', true, 'date')],
            'responses', 'hire-validations', 'hierarchy', 'rh-review', 'finance-review', 'direction-review' => [$select('decision', 'Décision / avis', ['À traiter', 'Favorable', 'Défavorable', 'À corriger']), $text('comment', 'Commentaire ou motif', false, 'textarea')],
            'assignment-b3', 'assignment-b4' => [$text('employee', 'Salarié', true), $text('position', 'Poste', true), $text('start', 'Date de début', true, 'date'), $text('manager', 'Responsable')],
            'contract-form' => [$text('employee', 'Salarié', true), $select('contract_type', 'Type de contrat', ['CE', 'CDD', 'CDI']), $text('start', 'Date de début', true, 'date'), $text('end', 'Date de fin', false, 'date'), $text('salary', 'Salaire (Ar)', true, 'number')],
            'contract-settings' => [$select('contract_type', 'Type de contrat', ['CE', 'CDD', 'CDI']), $text('duration', 'Durée maximale (mois)', false, 'number'), $text('renewals', 'Renouvellements maximum', true, 'number')],
            'onboarding-detail' => [$text('task', 'Tâche d’intégration', true), $select('status', 'Statut', ['À faire', 'En cours', 'Terminé']), $text('due', 'Échéance', false, 'date'), $text('comment', 'Responsable / commentaire')],
            'document-form' => [$text('employee', 'Salarié', true), $select('document_type', 'Type de document', ['Contrat signé', 'Pièce d’identité', 'Autre']), $text('file_name', 'Nom du fichier', true)],
            default => [$text('subject', 'Objet', true), $text('description', 'Description / commentaire', false, 'textarea'), $select('status', 'Statut', ['Brouillon', 'À traiter', 'Validé'])],
        };
    }

    public function page(string $slug): ?array
    {
        $screens = $this->screens();
        if (! isset($screens[$slug])) {
            return null;
        }

        $page = $screens[$slug];
        $page['slug'] = $slug;
        $page['rows'] = $this->rows($slug, $page);
        $page['saved'] = session()->get('demo_' . static::class . '_' . $slug) ?? [];
        return $page;
    }

    /** Valide puis conserve les données saisies dans la session de démonstration. */
    public function save(string $slug, array $input): array
    {
        $page = $this->page($slug);
        if ($page === null || ($page['kind'] ?? '') !== 'form') {
            return ['ok' => false, 'message' => 'Cet écran ne reçoit pas de formulaire.'];
        }

        $values = [];
        foreach ($page['fields'] as $field) {
            $name = $field['name'];
            $value = trim((string) ($input[$name] ?? ''));
            if (! empty($field['required']) && $value === '') {
                return ['ok' => false, 'message' => 'Le champ « ' . $field['label'] . ' » est obligatoire.'];
            }
            $values[$name] = $value;
        }

        $saved = session()->get('demo_' . static::class . '_' . $slug) ?? [];
        array_unshift($saved, ['values' => $values, 'saved_at' => date('Y-m-d H:i:s')]);
        session()->set('demo_' . static::class . '_' . $slug, $saved);
        return ['ok' => true, 'message' => 'Saisie enregistrée dans la session de démonstration.'];
    }

    private function rows(string $slug, array $page): array
    {
        if (($page['kind'] ?? '') !== 'list') {
            return [];
        }
        $saved = session()->get('demo_' . static::class . '_' . $slug) ?? [];
        $rows = [];
        foreach (array_slice($saved, 0, 5) as $item) {
            $values = $item['values'] ?? [];
            $rows[] = [date('ymd', strtotime($item['saved_at'] ?? 'now')) . '-' . count($rows), $values['subject'] ?? 'Nouvel élément', $values['status'] ?? 'Brouillon', date('d/m/Y', strtotime($item['saved_at'] ?? 'now'))];
        }
        return array_merge($rows, $page['sample'] ?? []);
    }
}
