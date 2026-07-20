<?php

namespace App\Services;

use App\Models\ClesModel;
use App\Models\FraisModel;
use App\Models\NumerosModel;
use App\Models\PrefixesModel;
use App\Models\TransactionsModel;
use App\Models\TransfertsModel;
use CodeIgniter\Pager\PagerInterface;
use DomainException;
use Throwable;

class MobileMoneyService
{
    private ClesModel $cles;
    private FraisModel $frais;
    private NumerosModel $numeros;
    private PrefixesModel $prefixes;
    private TransactionsModel $transactions;
    private TransfertsModel $transferts;
    private ?PagerInterface $paginateur = null;

    public function __construct()
    {
        $this->cles = new ClesModel();
        $this->frais = new FraisModel();
        $this->numeros = new NumerosModel();
        $this->prefixes = new PrefixesModel();
        $this->transactions = new TransactionsModel();
        $this->transferts = new TransfertsModel();
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

    public function ajouterTransaction(string $numero, float $montant, float $fraisAppliques = 0): int
    {
        $this->transactions->insert([
            'num' => $numero,
            'montant' => round($montant, 2),
            // Cette valeur est figée lors de l'opération et ne dépendra plus
            // des modifications futures apportées aux barèmes.
            'frais' => round($fraisAppliques, 2),
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
        $frais = $this->fraisPourMontant($montant);
        $total = $montant + $frais;
        // La transaction concerne seulement la cohérence des trois écritures.
        $baseDonnees = db_connect();
        $baseDonnees->transBegin();
        try {
            // Le contrôle est fait dans la transaction et inclut toujours les frais.
            $this->verifierSoldeSuffisant($expediteur, $total, 'transfert');
            if (! $this->creerNumeroSiAbsent($destinataire)) {
                throw new DomainException('Le numéro destinataire possède un préfixe inconnu.');
            }
            $idDebit = $this->ajouterTransaction($expediteur, -$total, $frais);
            $idCredit = $this->ajouterTransaction($destinataire, $montant);
            if ($this->transferts->insert([
                'idTransactionE' => $idDebit,
                'idTransactionD' => $idCredit,
            ]) === false) {
                throw new DomainException('Impossible d’enregistrer le transfert.');
            }
            $baseDonnees->transCommit();
        } catch (Throwable $exception) {
            $baseDonnees->transRollback();
            throw $exception;
        }
        return $frais;
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

    public function gainsFraisOperateur(string $operateur): array
    {
        $transactions = [];
        foreach ($this->transactions->findAll() as $transaction) {
            $transactions[(int) $transaction['id']] = $transaction;
        }

        $idsDebitsTransfert = [];
        $gains = [];
        foreach ($this->transferts->findAll() as $transfert) {
            $idDebit = (int) $transfert['idTransactionE'];
            $idCredit = (int) $transfert['idTransactionD'];
            $idsDebitsTransfert[$idDebit] = true;
            if (! isset($transactions[$idDebit], $transactions[$idCredit])) {
                continue;
            }
            $debit = $transactions[$idDebit];
            $fraisTransfert = (float) $debit['frais'];
            if ($this->operateurDuNumero($debit['num']) === $operateur && $fraisTransfert > 0) {
                $gains[] = $this->ligneGain($debit, 'Transfert', $fraisTransfert);
            }
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
