<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<a href="<?= site_url(ltrim($urlRetour, '/')) ?>" class="btn btn-outline-secondary btn-sm mb-3">← Retour</a>
<h1 class="h3 mb-4">Historique de <?= esc($numero) ?></h1>

<h2 class="h5">Transactions simples</h2>
<div class="card p-3 mb-3">
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Date</th><th>Type</th><th class="text-end">Montant débité/crédité</th><th class="text-end">Frais appliqués</th></tr></thead>
        <tbody>
        <?php foreach ($transactionsSimples as $transaction): ?>
            <tr>
                <td><?= esc($transaction['dateTransaction']) ?></td>
                <td><?= (float) $transaction['montant'] >= 0 ? 'Dépôt' : 'Retrait (frais inclus)' ?></td>
                <td class="text-end <?= (float) $transaction['montant'] >= 0 ? 'amount-positive' : 'amount-negative' ?>"><?= number_format((float) $transaction['montant'], 2, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((float) $transaction['frais'], 2, ',', ' ') ?> Ar</td>
            </tr>
        <?php endforeach ?>
        <?php if ($transactionsSimples === []): ?><tr><td colspan="4" class="text-center text-secondary">Aucune transaction simple.</td></tr><?php endif ?>
        </tbody>
    </table></div>
</div>
<?= $paginateurTransactions->links('transactions', 'default_full') ?>

<h2 class="h5 mt-5">Transferts</h2>
<div class="card p-3 mb-3">
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Date</th><th>Sens</th><th>Correspondant</th><th class="text-end">Montant</th><th class="text-end">Frais</th></tr></thead>
        <tbody>
        <?php foreach ($transferts as $transfert): $envoye = $transfert['expediteur'] === $numero; ?>
            <tr>
                <td><?= esc($transfert['dateTransaction']) ?></td>
                <td><?= $envoye ? 'Envoi' : 'Réception' ?></td>
                <td><?= esc($envoye ? $transfert['destinataire'] : $transfert['expediteur']) ?></td>
                <td class="text-end <?= $envoye ? 'amount-negative' : 'amount-positive' ?>"><?= $envoye ? '-' : '+' ?><?= number_format((float) $transfert['montant'], 2, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= $envoye ? number_format((float) $transfert['frais'], 2, ',', ' ') . ' Ar' : '—' ?></td>
            </tr>
        <?php endforeach ?>
        <?php if ($transferts === []): ?><tr><td colspan="5" class="text-center text-secondary">Aucun transfert.</td></tr><?php endif ?>
        </tbody>
    </table></div>
</div>
<?= $paginateurTransferts->links('transferts', 'default_full') ?>
<?= $this->endSection() ?>
