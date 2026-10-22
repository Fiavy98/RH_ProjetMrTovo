<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <div class="eyebrow">BLOC 1 · AVIS FINANCE</div>
        <h1>Avis Finance — Demande D-<?= esc(str_pad((string) $request->id, 3, '0', STR_PAD_LEFT)) ?></h1>
        <p>Examinez le budget transmis par les RH et enregistrez votre avis.</p>
    </div>
</div>
<section class="panel">
    <div class="panel-body">
        <p><strong>Intitulé :</strong> <?= esc($request->intitule) ?></p>
        <p><strong>Demandeur :</strong> <?= esc($request->demandeur_prenom . ' ' . $request->demandeur_nom) ?></p>
        <p><strong>Département :</strong> <?= esc($request->nom_departement) ?></p>
        <p><strong>Motif :</strong><br><?= nl2br(esc($request->motif)) ?></p>
        <p><strong>Budget prévisionnel transmis par RH :</strong></p>
        <p>Minimum : <?= esc($request->budget_min) ?> Ar &nbsp;&nbsp; Maximum : <?= esc($request->budget_max) ?> Ar</p>
    </div>
</section>
<section class="panel">
    <div class="panel-head"><h2 class="panel-title">Avis Finance</h2></div>
    <div class="panel-body">
        <form method="post" action="<?= site_url('bloc1/finance-review/' . $request->id) ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label for="decision">Avis Finance *</label>
                <select name="decision" id="decision" required>
                    <option value="">-- Choisir --</option>
                    <option value="valider">Oui, la demande peut poursuivre le circuit</option>
                    <option value="refuser">Non, la demande ne peut pas poursuivre le circuit</option>
                    <option value="complement">Retourner pour complément</option>
                </select>
            </div>
            <div class="field full">
                <label for="commentaire">Commentaire Finance *</label>
                <textarea name="commentaire" id="commentaire" required><?= esc(old('commentaire')) ?></textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Enregistrer l'avis</button>
                <a class="btn btn-secondary" href="<?= site_url('bloc1/finance-review') ?>">Retourner à la liste</a>
            </div>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
