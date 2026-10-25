<?php

/**
 * Helpers d'interface SIRH, repris des composants du template NovaRH.
 * Chargé automatiquement par BaseController::$helpers.
 *
 * Règle de sécurité : tout texte venant de la base ou d'un formulaire passe par esc().
 * Les fonctions ui_* qui renvoient du HTML échappent leurs paramètres texte ; seul le paramètre
 * $content de ui_panel() est du HTML déjà prêt (à construire avec les autres fonctions ui_*).
 */

if (! function_exists('ui_roles')) {
    function ui_roles(): array
    {
        return [
            'manager'   => ['label' => 'Manager'],
            'rh'        => ['label' => 'Ressources humaines'],
            'finance'   => ['label' => 'Finance'],
            'direction' => ['label' => 'Direction'],
            'admin'     => ['label' => 'Super administrateur'],
        ];
    }
}

if (! function_exists('ui_current_user')) {
    function ui_current_user(): array
    {
        $user = session()->get('auth_user');

        return is_array($user) ? $user : [
            'id' => 0, 'name' => 'Utilisateur', 'label' => '', 'department' => '', 'initials' => '—',
        ];
    }
}

if (! function_exists('ui_role')) {
    function ui_role(): string
    {
        $role = session()->get('role');

        return is_string($role) && isset(ui_roles()[$role]) ? $role : 'manager';
    }
}

if (! function_exists('ui_menu')) {
    /**
     * Construit le menu latéral : Accueil, un groupe par bloc et les outils de développement.
     */
    function ui_menu(): array
    {
        $role   = ui_role();
        $groups = [
            ['title' => 'Vue générale', 'items' => [['label' => 'Accueil', 'url' => '', 'icon' => '◧']]],
        ];

        foreach ([1, 2, 3, 4] as $n) {
            $file = APPPATH . "Config/Menu/bloc{$n}.php";

            if (is_file($file)) {
                $group = require $file;

                if (is_array($group)) {
                    $groups[] = $group;
                }
            }
        }

        if (ENVIRONMENT !== 'production') {
            $groups[] = ['title' => 'Développement', 'items' => [['label' => 'Contrôle de la base', 'url' => 'dev/db', 'icon' => '⚙']]];
        }

        $result = [];

        foreach ($groups as $group) {
            $items = array_values(array_filter(
                $group['items'] ?? [],
                static fn (array $item): bool => empty($item['roles']) || in_array($role, $item['roles'], true)
            ));

            if ($items !== []) {
                $group['items'] = $items;
                $result[]       = $group;
            }
        }

        return $result;
    }
}

if (! function_exists('ui_status_label')) {
    /** Libellé lisible d'un code de statut (valeurs en MAJUSCULES ou minuscules acceptées). */
    function ui_status_label(?string $status): string
    {
        $labels = [
            'brouillon' => 'Brouillon', 'signe' => 'Signé', 'en_cours' => 'En cours', 'termine' => 'Terminé',
            'annule' => 'Annulé', 'a_faire' => 'À faire', 'en_attente' => 'En attente', 'acceptee' => 'Acceptée',
            'refusee' => 'Refusée', 'publiee' => 'Publiée', 'cloturee' => 'Clôturée', 'preselectionnee' => 'Présélectionnée',
            'retenue' => 'Retenue', 'planifie' => 'Planifié', 'realise' => 'Réalisé', 'validee' => 'Validée',
            'oui' => 'Oui', 'non' => 'Non',
        ];

        $key = strtolower((string) $status);

        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}

if (! function_exists('ui_badge')) {
    /** Pastille colorée pour un statut (mêmes couleurs que le template). */
    function ui_badge(?string $status): string
    {
        $key = strtoupper((string) $status);

        if ($key === '') {
            return '<span class="badge gray">—</span>';
        }

        $color = 'gray';

        if (preg_match('/REFUS|REJET|ANNULE/', $key)) {
            $color = 'red';
        } elseif (preg_match('/APPROUV|RETEN|VALIDE|SIGNE|REALISE|TERMINE|ACCEPTEE|PUBLIEE|TRANSMISE|OUI/', $key)) {
            $color = 'green';
        } elseif (preg_match('/ATTENTE|PLANIF|EN_COURS|PRESELECTION|ENTRETIEN/', $key)) {
            $color = 'orange';
        }

        return '<span class="badge ' . $color . '">' . esc(ui_status_label($status)) . '</span>';
    }
}

if (! function_exists('ui_money')) {
    /** Montant en Ariary : 1500000 => « 1 500 000 Ar ». */
    function ui_money($amount): string
    {
        if ($amount === null || $amount === '') {
            return 'Non précisé';
        }

        return number_format((float) $amount, 0, ',', ' ') . ' Ar';
    }
}

if (! function_exists('ui_date')) {
    /** Date au format jj/mm/aaaa (accepte une Entity datetime, un objet date ou une chaîne). */
    function ui_date($value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $time = strtotime((string) $value);

        return $time === false ? '—' : date('d/m/Y', $time);
    }
}

if (! function_exists('ui_raw')) {
    /** Marque une cellule de ui_table() comme HTML déjà prêt (non échappé). */
    function ui_raw(string $html): array
    {
        return ['__html' => $html];
    }
}

if (! function_exists('ui_table')) {
    /**
     * Tableau simple. $rows = liste de lignes ; chaque cellule est un texte (échappé) ou ui_raw('<html>').
     */
    function ui_table(array $columns, array $rows, string $empty = 'Aucune donnée.'): string
    {
        $html = '<div class="table-wrap"><table><thead><tr>';

        foreach ($columns as $column) {
            $html .= '<th>' . esc($column) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        if ($rows === []) {
            $html .= '<tr><td colspan="' . count($columns) . '"><div class="empty"><span class="big">⌕</span>' . esc($empty) . '</div></td></tr>';
        }

        foreach ($rows as $row) {
            $html .= '<tr>';

            foreach ($row as $cell) {
                if (is_array($cell) && isset($cell['__html'])) {
                    $html .= '<td>' . $cell['__html'] . '</td>';
                } else {
                    $html .= '<td>' . (($cell === null || $cell === '') ? '—' : esc((string) $cell)) . '</td>';
                }
            }

            $html .= '</tr>';
        }

        return $html . '</tbody></table></div>';
    }
}

if (! function_exists('ui_panel')) {
    /** Panneau avec titre. $content est du HTML déjà prêt ; $title et $sub sont échappés. */
    function ui_panel(string $title, string $content, string $sub = ''): string
    {
        return '<section class="panel"><div class="panel-head"><div><h2 class="panel-title">' . esc($title) . '</h2>'
            . ($sub !== '' ? '<p class="panel-sub">' . esc($sub) . '</p>' : '')
            . '</div></div><div class="panel-body">' . $content . '</div></section>';
    }
}

if (! function_exists('ui_notice')) {
    /** Encadré d'information. $type : '', 'warn', 'success' ou 'danger'. */
    function ui_notice(string $text, string $type = ''): string
    {
        return '<div class="notice ' . esc($type, 'attr') . '">' . esc($text) . '</div>';
    }
}

if (! function_exists('ui_field')) {
    /**
     * Champ de formulaire (label + contrôle + aide), mêmes classes CSS que le template.
     *
     * $opts : required, readonly, disabled, full, hint, placeholder, min, max, step,
     *         options (tableau valeur => libellé, pour un <select>).
     * La valeur ressaisie après une erreur de validation (withInput) est réaffichée automatiquement.
     */
    function ui_field(string $name, string $label, string $type = 'text', $value = '', array $opts = []): string
    {
        $value = old($name, $value, false);
        $id    = 'f-' . $name;
        $attrs = (! empty($opts['required']) ? ' required' : '')
            . (! empty($opts['readonly']) ? ' readonly' : '')
            . (! empty($opts['disabled']) ? ' disabled' : '');

        if (isset($opts['options'])) {
            $control = '<select name="' . esc($name, 'attr') . '" id="' . esc($id, 'attr') . '"' . $attrs . '>';

            foreach ($opts['options'] as $optValue => $optLabel) {
                $selected = (string) $optValue === (string) $value ? ' selected' : '';
                $control .= '<option value="' . esc((string) $optValue, 'attr') . '"' . $selected . '>' . esc((string) $optLabel) . '</option>';
            }

            $control .= '</select>';
        } elseif ($type === 'textarea') {
            $control = '<textarea name="' . esc($name, 'attr') . '" id="' . esc($id, 'attr') . '"' . $attrs
                . (isset($opts['placeholder']) ? ' placeholder="' . esc($opts['placeholder'], 'attr') . '"' : '')
                . '>' . esc((string) $value) . '</textarea>';
        } else {
            $extra = '';

            foreach (['placeholder', 'min', 'max', 'step'] as $key) {
                if (isset($opts[$key])) {
                    $extra .= ' ' . $key . '="' . esc((string) $opts[$key], 'attr') . '"';
                }
            }

            $control = '<input type="' . esc($type, 'attr') . '" name="' . esc($name, 'attr') . '" id="' . esc($id, 'attr')
                . '" value="' . esc((string) $value, 'attr') . '"' . $attrs . $extra . '>';
        }

        return '<div class="field' . (! empty($opts['full']) ? ' full' : '') . '"><label for="' . esc($id, 'attr') . '">'
            . esc($label) . (! empty($opts['required']) ? ' *' : '') . '</label>' . $control
            . (isset($opts['hint']) ? '<small>' . esc($opts['hint']) . '</small>' : '') . '</div>';
    }
}
