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
    <div class="col-md-6 col-lg-4">
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
    <div class="col-md-6 col-lg-4">
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
    <div class="col-lg-4">
        <div class="card h-100 p-4">
            <h2 class="h5">Transférer</h2>
            <form method="post" action="<?= site_url('client/transfert') ?>">
                <?= csrf_field() ?>
                <label class="form-label" for="recipient-0">Destinataires</label>
                <div id="recipients">
                    <?php $anciensDestinataires = old('destinataires') ?: ['']; ?>
                    <?php foreach ((array) $anciensDestinataires as $index => $ancienDestinataire): ?>
                        <div class="input-group mb-2 recipient-row">
                            <input class="form-control" id="recipient-<?= (int) $index ?>" name="destinataires[]"
                                   inputmode="numeric" pattern="[0-9]{10}" maxlength="10"
                                   value="<?= esc($ancienDestinataire) ?>" required>
                            <button class="btn btn-outline-danger remove-recipient" type="button"
                                    aria-label="Supprimer ce destinataire">×</button>
                        </div>
                    <?php endforeach ?>
                </div>
                <button class="btn btn-outline-secondary btn-sm" id="add-recipient" type="button">+ Ajouter un numéro</button>
                <br>
                <label class="form-label mt-2" for="transfer-amount">Montant (Ar)</label>
                <input class="form-control" id="transfer-amount" name="montant" type="number" min="0.01" step="0.01"
                       value="<?= esc(old('montant')) ?>" required>
                <div class="form-check mt-3">
                    <input class="form-check-input" id="include-fees" name="inclure_frais_retrait" type="checkbox"
                           value="1" <?= old('inclure_frais_retrait') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="include-fees">Inclure les frais de retrait dans chaque envoi</label>
                </div>
                <small class="text-secondary d-block">Le montant est partagé équitablement. Les frais de transfert sont calculés par destinataire.</small>
                <button class="btn btn-primary w-100 mt-3">Transférer</button>
            </form>
        </div>
    </div>
</div>
<script>
(() => {
    const container = document.getElementById('recipients');
    const updateButtons = () => {
        const buttons = container.querySelectorAll('.remove-recipient');
        buttons.forEach(button => button.disabled = buttons.length === 1);
    };
    document.getElementById('add-recipient').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 recipient-row';
        row.innerHTML = '<input class="form-control" name="destinataires[]" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required>'
            + '<button class="btn btn-outline-danger remove-recipient" type="button" aria-label="Supprimer ce destinataire">×</button>';
        container.appendChild(row);
        row.querySelector('input').focus();
        updateButtons();
    });
    container.addEventListener('click', event => {
        if (event.target.classList.contains('remove-recipient')) {
            event.target.closest('.recipient-row').remove();
            updateButtons();
        }
    });
    updateButtons();
})();
</script>
<?= $this->endSection() ?>
