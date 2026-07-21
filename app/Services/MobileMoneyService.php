<?php

namespace App\Services;

use App\Models\ClesModel;
use App\Models\CommissionsModel;
use App\Models\FraisModel;
use App\Models\NumerosModel;
use App\Models\PrefixesModel;
use App\Models\PromotionModel;
use App\Models\TransactionsModel;
use App\Models\TransfertsModel;
use CodeIgniter\Pager\PagerInterface;
use DomainException;
use Throwable;

class MobileMoneyService
{
    private ClesModel $cles;
    private CommissionsModel $commissions;
    private FraisModel $frais;
    private NumerosModel $numeros;
    private PrefixesModel $prefixes;
    private TransactionsModel $transactions;
    private TransfertsModel $transferts;
    private PromotionModel $promotions;
    private ?PagerInterface $paginateur = null;

    public function __construct()
    {
        $this->cles = new ClesModel();
        $this->commissions = new CommissionsModel();
        $this->frais = new FraisModel();
        $this->numeros = new NumerosModel();
        $this->prefixes = new PrefixesModel();
        $this->transactions = new TransactionsModel();
        $this->transferts = new TransfertsModel();
        $this->promotions = new PromotionModel();
    }

    public function paginateur(): PagerInterface
    {
        $this->paginateur ??= service('pager');
        return $this->paginateur;
    }

    public function chercherCle(string $valeur): ?array
    {
        foreach ($this->cles->findAll() as $cle) {
            if ($cle['num'] === $valeur) {
                return $cle;
            }
        }
        return null;
    }

    public function operateurDuNumero(string $numero): ?string
    {
        $prefixeRecherche = substr($numero, 0, 3);
        foreach ($this->prefixes->findAll() as $prefixe) {
            if ($prefixe['num'] === $prefixeRecherche) {
                return $prefixe['operateur'];
            }
        }
        return null;
    }

    public function numeroExiste(string $numero): bool
    {
        foreach ($this->numeros->findAll() as $ligne) {
            if ($ligne['num'] === $numero) {
                return true;
            }
        }
        return false;
    }

    public function creerNumeroSiAbsent(string $numero): bool
    {
        // L'auto-inscription est interdite si le format ou le préfixe est inconnu.
        if (! preg_match('/^[0-9]{10}$/', $numero) || $this->operateurDuNumero($numero) === null) {
            return false;
        }

        return $this->numeroExiste($numero)
            || $this->numeros->insert(['num' => $numero], false) !== false;
    }

    public function fraisPourMontant(float $montant): float
    {
        foreach ($this->frais->findAll() as $bareme) {
            if ($montant >= (float) $bareme['montantMin'] && $montant <= (float) $bareme['montantMax']) {
                return (float) $bareme['montantFrais'];
            }
        }
        throw new DomainException('Aucun barème de frais ne couvre ce montant.');
    }

    public function baremesFrais(): array
    {
        $baremes = $this->frais->findAll();
        usort($baremes, static fn (array $a, array $b): int => (float) $a['montantMin'] <=> (float) $b['montantMin']);
        return $baremes;
    }

    public function ajouterBaremeFrais(float $minimum, float $maximum, float $montantFrais): int
    {
        $this->validerBaremeFrais($minimum, $maximum, $montantFrais);
        $this->frais->insert([
            'montantMin' => round($minimum, 2),
            'montantMax' => round($maximum, 2),
            'montantFrais' => round($montantFrais, 2),
        ]);
        return (int) $this->frais->getInsertID();
    }

    public function modifierBaremeFrais(int $id, float $minimum, float $maximum, float $montantFrais): bool
    {
        $existe = false;
        foreach ($this->frais->findAll() as $bareme) {
            if ((int) $bareme['id'] === $id) {
                $existe = true;
                break;
            }
        }
        if (! ($existe ?? false)) {
            return false;
        }
        $this->validerBaremeFrais($minimum, $maximum, $montantFrais, $id);
        return $this->frais->update($id, [
            'montantMin' => round($minimum, 2),
            'montantMax' => round($maximum, 2),
            'montantFrais' => round($montantFrais, 2),
        ]);
    }

    public function modifierPromotion(int $id, float $pourcentage) {
        return $this->promotions->update($id, [
            'pourcentage' => round($pourcentage, 2),
        ]);
    }

    public function supprimerBaremeFrais(int $id): bool
    {
        foreach ($this->frais->findAll() as $bareme) {
            if ((int) $bareme['id'] === $id) {
                return $this->frais->delete($id);
            }
        }
        return false;
    }

    private function validerBaremeFrais(
        float $minimum,
        float $maximum,
        float $montantFrais,
        ?int $idIgnore = null
    ): void {
        if ($minimum < 0 || $maximum <= $minimum) {
            throw new DomainException('Le maximum doit être strictement supérieur au minimum positif ou nul.');
        }
        if ($montantFrais <= 0) {
            throw new DomainException('Le montant des frais doit être positif.');
        }

        // Deux intervalles inclusifs se chevauchent si chacun commence avant la fin de l'autre.
        foreach ($this->frais->findAll() as $bareme) {
            if ($idIgnore !== null && (int) $bareme['id'] === $idIgnore) {
                continue;
            }
            $chevauche = $minimum <= (float) $bareme['montantMax']
                && $maximum >= (float) $bareme['montantMin'];
            if ($chevauche) {
                throw new DomainException('Cette plage chevauche un barème existant.');
            }
        }
    }

    public function solde(string $numero): float
    {
        $solde = 0.0;
        foreach ($this->transactions->findAll() as $transaction) {
            if ($transaction['num'] === $numero) {
                $solde += (float) $transaction['montant'];
            }
        }
        return round($solde, 2);
    }

    public function ajouterTransaction(
        string $numero,
        float $montant,
        float $fraisAppliques = 0,
        float $commission = 0
    ): int
    {
        $this->transactions->insert([
            'num' => $numero,
            'montant' => round($montant, 2),
            // Cette valeur est figée lors de l'opération et ne dépendra plus
            // des modifications futures apportées aux barèmes.
            'frais' => round($fraisAppliques, 2),
            'commission' => round($commission, 2),
            'dateTransaction' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->transactions->getInsertID();
    }

    public function retirer(string $numero, float $montant): float
    {
        $frais = $this->fraisPourMontant($montant);
        $total = $montant + $frais;

        $baseDonnees = db_connect();
        $baseDonnees->transBegin();
        try {
            $this->verifierSoldeSuffisant($numero, $total, 'retrait');
            $this->ajouterTransaction($numero, -$total, $frais);
            $baseDonnees->transCommit();
        } catch (Throwable $exception) {
            $baseDonnees->transRollback();
            throw $exception;
        }
        return $frais;
    }

    public function transferer(string $expediteur, string $destinataire, float $montant): float
    {
        return $this->transfererMultiple($expediteur, [$destinataire], $montant, false)['fraisTransfert'];
    }

    /**
     * Crée un débit global et un crédit par destinataire.
     *
     * La commission totale est portée par le débit global. Sa part par destinataire
     * est aussi figée sur le crédit pour permettre une ventilation fiable des gains.
     */
    public function transfererMultiple(
        string $expediteur,
        array $destinataires,
        float $montantTotal,
        bool $inclureFraisRetrait = false
    ): array {
        $destinataires = array_values(array_unique($destinataires));
        if ($destinataires === []) {
            throw new DomainException('Ajoutez au moins un destinataire.');
        }

        $operateurExpediteur = $this->operateurDuNumero($expediteur);
        if ($operateurExpediteur === null) {
            throw new DomainException('Le préfixe de l’expéditeur est inconnu.');
        }

        $nombre = count($destinataires);
        $partStandard = round($montantTotal / $nombre, 2);
        $parts = array_fill(0, $nombre, $partStandard);
        $parts[$nombre - 1] = round($montantTotal - ($partStandard * ($nombre - 1)), 2);
        $pourcentageCommission = $this->pourcentageCommission();
        $credits = [];
        $totalFraisTransfert = 0.0;
        $totalCommission = 0.0;
        $totalCredits = 0.0;

        foreach ($destinataires as $index => $destinataire) {
            if (! preg_match('/^[0-9]{10}$/', $destinataire) || $destinataire === $expediteur) {
                throw new DomainException('La liste des destinataires contient un numéro invalide.');
            }
            $operateurDestinataire = $this->operateurDuNumero($destinataire);
            if ($operateurDestinataire === null) {
                throw new DomainException("Le préfixe de {$destinataire} est inconnu.");
            }

            $part = $parts[$index];
            $fraisUnitaire = $this->fraisPourMontant($part);
            $montantCredite = round($part + ($inclureFraisRetrait ? $fraisUnitaire : 0), 2);
            // Le supplément envoyé pour couvrir le retrait appartient au
            // destinataire : il ne constitue jamais une base de gain.
            $commission = $operateurDestinataire !== $operateurExpediteur
                ? round($part * $pourcentageCommission / 100, 2)
                : 0.0;
            $credits[] = compact('destinataire', 'montantCredite', 'commission');
            $prom = (float) $this->promotions->first();
            if ($operateurDestinataire === $operateurExpediteur) {
                $prom = $fraisUnitaire * (1 - ($prom / 10));
                $totalFraisTransfert += $prom;
            } else {
                $totalFraisTransfert += $fraisUnitaire;
            }
            $totalCommission += $commission;
            $totalCredits += $montantCredite;
        }

        $totalFraisTransfert = round($totalFraisTransfert, 2);
        $totalCommission = round($totalCommission, 2);
        $totalDebite = round($totalCredits + $totalFraisTransfert + $totalCommission, 2);
        $baseDonnees = db_connect();
        $baseDonnees->transBegin();
        try {
            $this->verifierSoldeSuffisant($expediteur, $totalDebite, 'transfert');
            foreach ($destinataires as $destinataire) {
                if (! $this->creerNumeroSiAbsent($destinataire)) {
                    throw new DomainException("Impossible de créer le numéro {$destinataire}.");
                }
            }
            $idDebit = $this->ajouterTransaction(
                $expediteur,
                -$totalDebite,
                $totalFraisTransfert,
                $totalCommission
            );
            foreach ($credits as $credit) {
                // Le crédit porte la commission ventilée uniquement comme métadonnée.
                // Son montant n'est jamais compté une seconde fois dans le solde.
                $idCredit = $this->ajouterTransaction(
                    $credit['destinataire'],
                    $credit['montantCredite'],
                    0,
                    $credit['commission']
                );
                if ($this->transferts->insert([
                    'idTransactionE' => $idDebit,
                    'idTransactionD' => $idCredit,
                ]) === false) {
                    throw new DomainException('Impossible d’enregistrer un transfert.');
                }
            }
            $baseDonnees->transCommit();
        } catch (Throwable $exception) {
            $baseDonnees->transRollback();
            throw $exception;
        }
        return [
            'fraisTransfert' => $totalFraisTransfert,
            'commission' => $totalCommission,
            'totalDebite' => $totalDebite,
        ];
    }

    public function pourcentageCommission(): float
    {
        $ligne = $this->commissions->first();
        return $ligne === null ? 0.0 : (float) $ligne['pourcentage'];
    }

    public function numerosDeOperateur(string $operateur, int $elementsParPage = 10): array
    {
        $prefixesAutorises = [];
        foreach ($this->prefixes->findAll() as $prefixe) {
            if ($prefixe['operateur'] === $operateur) {
                $prefixesAutorises[] = $prefixe['num'];
            }
        }

        $soldes = [];
        foreach ($this->transactions->findAll() as $transaction) {
            $numero = $transaction['num'];
            $soldes[$numero] = ($soldes[$numero] ?? 0) + (float) $transaction['montant'];
        }

        $resultat = [];
        foreach ($this->numeros->findAll() as $ligne) {
            if (in_array(substr($ligne['num'], 0, 3), $prefixesAutorises, true)) {
                $resultat[] = ['num' => $ligne['num'], 'solde' => round($soldes[$ligne['num']] ?? 0, 2)];
            }
        }
        usort($resultat, static fn (array $a, array $b): int => strcmp($a['num'], $b['num']));
        return $this->paginer($resultat, 'numeros', $elementsParPage);
    }

    public function numeroAppartientAOperateur(string $numero, string $operateur): bool
    {
        return $this->numeroExiste($numero) && $this->operateurDuNumero($numero) === $operateur;
    }

    public function historique(string $numero, int $elementsParPage = 10): array
    {
        $toutesTransactions = $this->transactions->findAll();
        $transactionsParId = [];
        foreach ($toutesTransactions as $transaction) {
            $transactionsParId[(int) $transaction['id']] = $transaction;
        }

        $idsTransactionsTransfert = [];
        $historiqueTransferts = [];
        foreach ($this->transferts->findAll() as $transfert) {
            $idDebit = (int) $transfert['idTransactionE'];
            $idCredit = (int) $transfert['idTransactionD'];
            $idsTransactionsTransfert[$idDebit] = true;
            $idsTransactionsTransfert[$idCredit] = true;
            if (! isset($transactionsParId[$idDebit], $transactionsParId[$idCredit])) {
                continue;
            }
            $debit = $transactionsParId[$idDebit];
            $credit = $transactionsParId[$idCredit];
            if ($debit['num'] !== $numero && $credit['num'] !== $numero) {
                continue;
            }
            $historiqueTransferts[] = [
                'id' => $transfert['id'],
                'expediteur' => $debit['num'],
                'destinataire' => $credit['num'],
                'montantDebite' => abs((float) $debit['montant']),
                'montant' => (float) $credit['montant'],
                'frais' => (float) $debit['frais'],
                'dateTransaction' => $debit['dateTransaction'],
            ];
        }

        $transactionsSimples = [];
        foreach ($toutesTransactions as $transaction) {
            if ($transaction['num'] === $numero && ! isset($idsTransactionsTransfert[(int) $transaction['id']])) {
                $transactionsSimples[] = $transaction;
            }
        }
        $trierPlusRecent = static fn (array $a, array $b): int => strcmp($b['dateTransaction'], $a['dateTransaction']);
        usort($transactionsSimples, $trierPlusRecent);
        usort($historiqueTransferts, $trierPlusRecent);

        return [
            'transactionsSimples' => $this->paginer($transactionsSimples, 'transactions', $elementsParPage),
            'transferts' => $this->paginer($historiqueTransferts, 'transferts', $elementsParPage),
        ];
    }

    public function tableauGainsOperateur(string $operateur): array
    {
        $lignes = [];
        foreach ($this->operateurs() as $operateurSource) {
            $details = $this->collecterDetailsGains($operateur, $operateurSource);
            $colonne = $operateurSource === $operateur ? 'frais' : 'commission';
            $lignes[] = [
                'operateur' => $operateurSource,
                'montant' => round(array_sum(array_column($details, $colonne)), 2),
            ];
        }
        return $lignes;
    }

    public function detailsGainsOperateur(
        string $operateur,
        string $operateurSource,
        int $elementsParPage = 10
    ): array {
        if (! in_array($operateurSource, $this->operateurs(), true)) {
            throw new DomainException('Opérateur inconnu.');
        }
        return $this->paginer(
            $this->collecterDetailsGains($operateur, $operateurSource),
            'gains',
            $elementsParPage
        );
    }

    private function collecterDetailsGains(string $operateur, string $operateurSource): array
    {
        $transactions = [];
        foreach ($this->transactions->findAll() as $transaction) {
            $transactions[(int) $transaction['id']] = $transaction;
        }

        $idsDebitsTransfert = [];
        $debitsDejaAjoutes = [];
        $gains = [];
        foreach ($this->transferts->findAll() as $transfert) {
            $idDebit = (int) $transfert['idTransactionE'];
            $idCredit = (int) $transfert['idTransactionD'];
            $idsDebitsTransfert[$idDebit] = true;
            if (! isset($transactions[$idDebit], $transactions[$idCredit])) {
                continue;
            }
            $debit = $transactions[$idDebit];
            $credit = $transactions[$idCredit];
            $operateurDebit = $this->operateurDuNumero($debit['num']);
            $operateurCredit = $this->operateurDuNumero($credit['num']);

            if ($operateurSource === $operateur) {
                $fraisTransfert = (float) $debit['frais'];
                if ($operateurDebit === $operateur
                    && $fraisTransfert > 0
                    && ! isset($debitsDejaAjoutes[$idDebit])) {
                    $debitsDejaAjoutes[$idDebit] = true;
                    $gains[] = $this->ligneGain($debit, 'Transfert', $fraisTransfert);
                }
                continue;
            }

            // Correction métier : un gain provenant d'un opérateur tiers est un
            // transfert entrant (tiers -> opérateur en session), jamais un sortant.
            if ($operateurDebit === $operateurSource && $operateurCredit === $operateur) {
                $gains[] = [
                    'id' => $transfert['id'],
                    'dateTransaction' => $debit['dateTransaction'],
                    'expediteur' => $debit['num'],
                    'destinataire' => $credit['num'],
                    'montant' => (float) $credit['montant'],
                    'commission' => (float) $credit['commission'],
                ];
            }
        }

        if ($operateurSource !== $operateur) {
            usort($gains, static fn (array $a, array $b): int => strcmp($b['dateTransaction'], $a['dateTransaction']));
            return $gains;
        }

        foreach ($transactions as $transaction) {
            if ((float) $transaction['montant'] >= 0
                || isset($idsDebitsTransfert[(int) $transaction['id']])
                || $this->operateurDuNumero($transaction['num']) !== $operateur) {
                continue;
            }
            $fraisRetrait = (float) $transaction['frais'];
            if ($fraisRetrait > 0) {
                $gains[] = $this->ligneGain($transaction, 'Retrait', $fraisRetrait);
            }
        }
        usort($gains, static fn (array $a, array $b): int => strcmp($b['dateTransaction'], $a['dateTransaction']));
        return $gains;
    }

    private function operateurs(): array
    {
        $operateurs = array_values(array_unique(array_column($this->prefixes->findAll(), 'operateur')));
        sort($operateurs, SORT_NATURAL | SORT_FLAG_CASE);
        return $operateurs;
    }

    private function ligneGain(array $transaction, string $type, float $frais): array
    {
        return [
            'id' => $transaction['id'],
            'num' => $transaction['num'],
            'dateTransaction' => $transaction['dateTransaction'],
            'type' => $type,
            'frais' => $frais,
        ];
    }

    private function verifierSoldeSuffisant(string $numero, float $totalAvecFrais, string $operation): void
    {
        if ($this->solde($numero) < $totalAvecFrais) {
            throw new DomainException("Solde insuffisant pour le {$operation}, frais inclus.");
        }
    }

    private function paginer(array $elements, string $groupe, int $elementsParPage): array
    {
        $requete = service('request');
        $pageDemandee = method_exists($requete, 'getGet') ? $requete->getGet('page_' . $groupe) : 1;
        $page = max(1, (int) $pageDemandee);
        $total = count($elements);
        $this->paginateur()->store($groupe, $page, $elementsParPage, $total);
        return array_slice($elements, ($page - 1) * $elementsParPage, $elementsParPage);
    }
}
