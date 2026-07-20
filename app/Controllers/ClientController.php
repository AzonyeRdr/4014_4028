<?php

namespace App\Controllers;

use App\Services\MobileMoneyService;
use DomainException;

class ClientController extends BaseController
{
    private MobileMoneyService $serviceMobile;

    public function __construct()
    {
        $this->serviceMobile = new MobileMoneyService();
    }

    public function tableauDeBord()
    {
        $numero = (string) session('client_num');
        return view('client/dashboard', [
            'numero' => $numero,
            'solde' => $this->serviceMobile->solde($numero),
        ]);
    }

    public function deposer()
    {
        $montant = $this->lireMontantPositif();
        if (! is_float($montant)) {
            return $montant;
        }

        $this->serviceMobile->ajouterTransaction((string) session('client_num'), $montant);
        return redirect()->to('/client')->with('success', 'Dépôt effectué avec succès.');
    }

    public function retirer()
    {
        $montant = $this->lireMontantPositif();
        if (! is_float($montant)) {
            return $montant;
        }

        try {
            $numero = (string) session('client_num');
            $frais = $this->serviceMobile->retirer($numero, $montant);
            return redirect()->to('/client')->with('success', "Retrait effectué. Frais : {$frais} Ar.");
        } catch (DomainException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function transferer()
    {
        $montant = $this->lireMontantPositif();
        if (! is_float($montant)) {
            return $montant;
        }

        $expediteur = (string) session('client_num');
        $destinataires = $this->request->getPost('destinataires');
        $destinataires = is_array($destinataires) ? array_values(array_unique(array_map('trim', $destinataires))) : [];
        if ($destinataires === []) {
            return redirect()->back()->withInput()->with('error', 'Ajoutez au moins un destinataire.');
        }
        foreach ($destinataires as $destinataire) {
            if (! preg_match('/^[0-9]{10}$/', $destinataire)) {
                return redirect()->back()->withInput()->with('error', 'Chaque destinataire doit contenir exactement 10 chiffres.');
            }
            if ($destinataire === $expediteur) {
                return redirect()->back()->withInput()->with('error', 'Votre propre numéro ne peut pas être destinataire.');
            }
            if ($this->serviceMobile->operateurDuNumero($destinataire) === null) {
                return redirect()->back()->withInput()->with('error', "Le préfixe de {$destinataire} est inconnu.");
            }
        }

        try {
            $inclureFrais = $this->request->getPost('inclure_frais_retrait') === '1';
            $resume = $this->serviceMobile->transfererMultiple($expediteur, $destinataires, $montant, $inclureFrais);
            return redirect()->to('/client')->with('success', sprintf(
                'Transfert effectué vers %d destinataire(s). Frais de transfert : %.2f Ar. Commission : %.2f Ar. Total débité : %.2f Ar.',
                count($destinataires),
                $resume['fraisTransfert'],
                $resume['commission'],
                $resume['totalDebite']
            ));
        } catch (DomainException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            log_message('error', 'Échec du transfert : {message}', ['message' => $exception->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Le transfert a échoué. Réessayez.');
        }
    }

    public function historique()
    {
        $numero = (string) session('client_num');
        $historique = $this->serviceMobile->historique($numero);
        return view('history/index', [
            'numero' => $numero,
            'transactionsSimples' => $historique['transactionsSimples'],
            'paginateurTransactions' => $this->serviceMobile->paginateur(),
            'transferts' => $historique['transferts'],
            'paginateurTransferts' => $this->serviceMobile->paginateur(),
            'urlRetour' => '/client',
        ]);
    }

    private function lireMontantPositif(): float|\CodeIgniter\HTTP\RedirectResponse
    {
        $valeurBrute = str_replace(',', '.', trim((string) $this->request->getPost('montant')));
        if (! is_numeric($valeurBrute) || ($montant = (float) $valeurBrute) <= 0) {
            return redirect()->back()->withInput()->with('error', 'Le montant saisi doit être strictement positif.');
        }
        return round($montant, 2);
    }
}
