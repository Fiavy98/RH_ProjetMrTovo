<?php
$base = 'bloc' . $bloc;
$eyebrow = 'BLOC ' . $bloc . ' · ' . strtoupper($page['title']);
?>
<div class="page-head">
    <div><div class="eyebrow"><?= esc($eyebrow) ?></div><h1><?= esc($page['title']) ?></h1><p><?= esc($page['description']) ?></p></div>
</div>

<section class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Écrans du bloc</h2><p class="panel-sub">Navigation de référence pour répartir le travail de l’équipe.</p></div></div>
    <div class="panel-body"><div class="action-row">
        <?php foreach ($screens as $slug => $screen): ?>
            <a class="btn <?= $slug === $page['slug'] ? 'btn-primary' : 'btn-secondary' ?>" href="<?= site_url($base . '/' . $slug) ?>"><?= esc($screen['title']) ?></a>
        <?php endforeach; ?>
    </div></div>
</section>

<?php if ($page['kind'] === 'dashboard'): ?>
    <div class="cards">
        <div class="stat"><div class="stat-ico">▤</div><div class="num"><?= count($screens) ?></div><div class="label">Écrans spécifiés</div><div class="hint">Parcours de démonstration</div></div>
        <div class="stat"><div class="stat-ico">◷</div><div class="num">4</div><div class="label">Blocs partagés</div><div class="hint">Même structure CodeIgniter</div></div>
        <div class="stat"><div class="stat-ico">✎</div><div class="num">Session</div><div class="label">Mode prototype</div><div class="hint">Les formulaires ne modifient pas PostgreSQL</div></div>
    </div>
<?php elseif ($page['kind'] === 'form'): ?>
    <?= ui_panel('Formulaire de démonstration', '<form method="post" action="' . site_url($base . '/' . $page['slug']) . '">' . csrf_field()
        . '<div class="form-grid">' . implode('', array_map(static function (array $field): string {
            $options = [];
            if (isset($field['options'])) {
                foreach ($field['options'] as $option) $options[$option] = $option;
            }
            return ui_field($field['name'], $field['label'], $field['type'], '', array_filter([
                'required' => $field['required'] ?? false,
                'full' => $field['type'] === 'textarea',
                'options' => $options ?: null,
            ], static fn ($value) => $value !== null));
        }, $page['fields'])) . '</div><div class="form-actions"><button class="btn btn-primary" type="submit">Enregistrer</button></div></form>',
        'Exemple de formulaire commun : à spécialiser avec les champs et validations de votre bloc.') ?>
    <?php if ($page['saved'] !== []): ?>
        <?= ui_notice('Dernière saisie conservée dans la session de démonstration.', 'success') ?>
        <?= ui_panel('Saisies récentes', ui_table(['Objet', 'Description', 'Statut'], array_map(static fn ($item) => [
            $item['values']['subject'] ?? '—', $item['values']['description'] ?? '—', $item['values']['status'] ?? '—',
        ], array_slice($page['saved'], 0, 5)))) ?>
    <?php endif; ?>
<?php else: ?>
    <?= ui_panel('Données de démonstration', ui_table(array_column($page['columns'], 'label'), $page['rows']),
        'Structure d’écran alignée sur le prototype statique. Les données d’exemple seront remplacées par les Models du bloc.') ?>
<?php endif; ?>
