<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<a href="<?= site_url('operator/gains') ?>" class="btn btn-outline-secondary btn-sm mb-3">← Tableau des gains</a>
<h1 class="h3 mb-1">Détails des gains — <?= esc($operateurSource) ?></h1>
<p class="text-secondary mb-4">
    <?= $estPropreOperateur
        ? 'Frais de retrait et de transfert perçus sur votre réseau.'
        : 'Transferts entrants de ' . esc($operateurSource) . ' vers ' . esc($operateur) . '.' ?>
</p>
<div class="card p-3">
    <div class="table-responsive"><table class="table mb-0">
    <?php if ($estPropreOperateur): ?>
        <thead><tr><th>Date</th><th>Numéro</th><th>Opération</th><th class="text-end">Frais gagnés</th></tr></thead>
        <tbody><?php foreach ($details as $detail): ?><tr>
            <td><?= esc($detail['dateTransaction']) ?></td><td><?= esc($detail['num']) ?></td><td><?= esc($detail['type']) ?></td>
            <td class="text-end amount-positive"><?= number_format((float) $detail['frais'], 2, ',', ' ') ?> Ar</td>
        </tr><?php endforeach ?>
        <?php if ($details === []): ?><tr><td colspan="4" class="text-center text-secondary">Aucun frais perçu.</td></tr><?php endif ?></tbody>
    <?php else: ?>
        <thead><tr><th>Date</th><th>Expéditeur</th><th>Destinataire</th><th class="text-end">Montant transféré</th><th class="text-end">Gain</th></tr></thead>
        <tbody><?php foreach ($details as $detail): ?><tr>
            <td><?= esc($detail['dateTransaction']) ?></td><td><?= esc($detail['expediteur']) ?></td><td><?= esc($detail['destinataire']) ?></td>
            <td class="text-end"><?= number_format((float) $detail['montant'], 2, ',', ' ') ?> Ar</td>
            <td class="text-end amount-positive"><?= number_format((float) $detail['commission'], 2, ',', ' ') ?> Ar</td>
        </tr><?php endforeach ?>
        <?php if ($details === []): ?><tr><td colspan="5" class="text-center text-secondary">Aucun transfert entrant.</td></tr><?php endif ?></tbody>
    <?php endif ?>
    </table></div>
</div>
<?php if ($paginateur->getPageCount('gains') > 1): ?>
    <div class="mt-3"><?= $paginateur->links('gains', 'default_full') ?></div>
<?php endif ?>
<?= $this->endSection() ?>
