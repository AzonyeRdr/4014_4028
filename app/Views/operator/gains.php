<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<a href="<?= site_url('operator/numeros') ?>" class="btn btn-outline-secondary btn-sm mb-3">← Liste des numéros</a>
<div class="d-flex justify-content-between align-items-end mb-4">
    <div><p class="text-secondary mb-1"><?= esc($operateur) ?></p><h1 class="h3 mb-0">Gains par opérateur</h1></div>
    <div class="text-end"><small class="text-secondary">Gain total</small><div class="h2 text-success mb-0"><?= number_format((float) $gainTotal, 2, ',', ' ') ?> Ar</div></div>
</div>
<div class="card p-3 mb-5">
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Opérateur</th><th class="text-end">Montant total des gains</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        <?php foreach ($tableauGains as $gain): ?><tr>
            <td><?= esc($gain['operateur']) ?><?= $gain['operateur'] === $operateur ? ' (votre réseau)' : '' ?></td>
            <td class="text-end amount-positive"><?= number_format((float) $gain['montant'], 2, ',', ' ') ?> Ar</td>
            <td class="text-end"><a class="btn btn-outline-primary btn-sm" href="<?= site_url('operator/gains/details/' . rawurlencode($gain['operateur'])) ?>">Voir les détails</a></td>
        </tr><?php endforeach ?>
        <?php if ($tableauGains === []): ?><tr><td colspan="3" class="text-center text-secondary">Aucun opérateur.</td></tr><?php endif ?>
        </tbody>
    </table></div>
</div>

<h2 class="h4 mb-3">Barèmes de frais</h2>
<div class="card p-3 mb-4">
    <h3 class="h5">Ajouter une plage</h3>
    <form class="row g-3 align-items-end" method="post" action="<?= site_url('operator/frais') ?>">
        <?= csrf_field() ?>
        <div class="col-md-3">
            <label class="form-label" for="new-min">Montant minimum</label>
            <input class="form-control" id="new-min" name="montantMin" type="number" min="0" step="0.01" value="<?= esc(old('montantMin')) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="new-max">Montant maximum</label>
            <input class="form-control" id="new-max" name="montantMax" type="number" min="0.01" step="0.01" value="<?= esc(old('montantMax')) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="new-fee">Frais</label>
            <input class="form-control" id="new-fee" name="montantFrais" type="number" min="0" step="0.01" value="<?= esc(old('montantFrais')) ?>" required>
        </div>
        <div class="col-md-3"><button class="btn btn-success w-100">Ajouter</button></div>
    </form>
</div>
<?php foreach ($baremesFrais as $bareme): ?>
    <form id="fee-<?= (int) $bareme['id'] ?>" method="post" action="<?= site_url('operator/frais/' . $bareme['id']) ?>"><?= csrf_field() ?></form>
<?php endforeach ?>
<div class="card p-3"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Montant minimum</th><th>Montant maximum</th><th>Frais</th><th>Actions</th></tr></thead>
    <tbody><?php foreach ($baremesFrais as $bareme): $idFormulaire = 'fee-' . (int) $bareme['id']; ?>
    <tr>
        <td><input form="<?= $idFormulaire ?>" class="form-control fee-input" name="montantMin" type="number" min="0" step="0.01" value="<?= esc($bareme['montantMin']) ?>" required></td>
        <td><input form="<?= $idFormulaire ?>" class="form-control fee-input" name="montantMax" type="number" min="0.01" step="0.01" value="<?= esc($bareme['montantMax']) ?>" required></td>
        <td><input form="<?= $idFormulaire ?>" class="form-control fee-input" name="montantFrais" type="number" min="0" step="0.01" value="<?= esc($bareme['montantFrais']) ?>" required></td>
        <td><div class="d-flex gap-2">
            <button form="<?= $idFormulaire ?>" class="btn btn-primary btn-sm">Modifier</button>
            <form method="post" action="<?= site_url('operator/frais/' . $bareme['id'] . '/supprimer') ?>" onsubmit="return confirm('Supprimer cette plage ?')">
                <?= csrf_field() ?><button class="btn btn-outline-danger btn-sm">Supprimer</button>
            </form>
        </div></td>
    </tr><?php endforeach ?>
    <?php if ($baremesFrais === []): ?><tr><td colspan="4" class="text-center text-secondary">Aucune plage de frais.</td></tr><?php endif ?></tbody>
</table></div></div>

<h2 class="h4 mb-3">Promotion sur les frais</h2>
<form action="promotion" method="post"></form>
<?= $this->endSection() ?>
