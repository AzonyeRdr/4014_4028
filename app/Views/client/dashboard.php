<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-secondary mb-1">Numéro connecté</p>
        <h1 class="h3 mb-0"><?= esc($numero) ?></h1>
    </div>
    <div class="text-end">
        <p class="text-secondary mb-1">Solde actuel</p>
        <div class="display-6 fw-semibold"><?= number_format($solde, 2, ',', ' ') ?> Ar</div>
    </div>
</div>
<a class="btn btn-outline-primary mb-4" href="<?= site_url('client/historique') ?>">Voir mon historique</a>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100 p-4">
            <h2 class="h5">Déposer</h2>
            <form method="post" action="<?= site_url('client/depot') ?>">
                <?= csrf_field() ?>
                <label class="form-label" for="deposit-amount">Montant (Ar)</label>
                <input class="form-control" id="deposit-amount" name="montant" type="number" min="0.01" step="0.01" required>
                <button class="btn btn-success w-100 mt-3">Déposer</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-4">
            <h2 class="h5">Retirer</h2>
            <form method="post" action="<?= site_url('client/retrait') ?>">
                <?= csrf_field() ?>
                <label class="form-label" for="withdraw-amount">Montant (Ar)</label>
                <input class="form-control" id="withdraw-amount" name="montant" type="number" min="0.01" step="0.01" required>
                <small class="text-secondary">Les frais sont ajoutés au débit.</small>
                <button class="btn btn-warning w-100 mt-3">Retirer</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-4">
            <h2 class="h5">Transférer</h2>
            <form method="post" action="<?= site_url('client/transfert') ?>">
                <?= csrf_field() ?>
                <label class="form-label" for="recipient">Destinataire</label>
                <input class="form-control" id="recipient" name="destinataire" inputmode="numeric"
                       pattern="[0-9]{10}" maxlength="10" value="<?= esc(old('destinataire')) ?>" required>
                <label class="form-label mt-2" for="transfer-amount">Montant (Ar)</label>
                <input class="form-control" id="transfer-amount" name="montant" type="number" min="0.01" step="0.01" required>
                <button class="btn btn-primary w-100 mt-3">Transférer</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
