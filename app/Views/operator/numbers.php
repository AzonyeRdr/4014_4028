<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-secondary mb-1">Espace opérateur</p>
        <h1 class="h3 mb-0"><?= esc($operateur) ?> — Numéros</h1>
    </div>
    <a class="btn btn-success" href="<?= site_url('operator/gains') ?>">Voir les gains</a>
</div>
<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Numéro</th><th class="text-end">Solde actuel</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($numeros as $numero): ?>
                <tr>
                    <td><?= esc($numero['num']) ?></td>
                    <td class="text-end"><?= number_format((float) $numero['solde'], 2, ',', ' ') ?> Ar</td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('operator/numeros/' . $numero['num'] . '/historique') ?>">Voir l’historique</a></td>
                </tr>
            <?php endforeach ?>
            <?php if ($numeros === []): ?><tr><td colspan="3" class="text-center text-secondary py-4">Aucun numéro.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3"><?= $paginateur->links('numeros', 'default_full') ?></div>
<?= $this->endSection() ?>
