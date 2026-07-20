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
        $destinataire = trim((string) $this->request->getPost('destinataire'));
        if (! preg_match('/^[0-9]{10}$/', $destinataire)) {
            return redirect()->back()->withInput()->with('error', 'Le destinataire doit contenir exactement 10 chiffres.');
        }
        if ($destinataire === $expediteur) {
            return redirect()->back()->withInput()->with('error', 'Vous ne pouvez pas effectuer un transfert vers votre propre numéro.');
        }
        if ($this->serviceMobile->operateurDuNumero($destinataire) === null) {
            return redirect()->back()->withInput()->with('error', 'Le préfixe du destinataire est inconnu.');
        }

        try {
            $frais = $this->serviceMobile->transferer($expediteur, $destinataire, $montant);
            return redirect()->to('/client')->with('success', "Transfert effectué. Frais : {$frais} Ar.");
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
