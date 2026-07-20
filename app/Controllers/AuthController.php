<?php

namespace App\Controllers;

use App\Services\MobileMoneyService;

class AuthController extends BaseController
{
    private MobileMoneyService $serviceMobile;

    public function __construct()
    {
        $this->serviceMobile = new MobileMoneyService();
    }

    public function connexion()
    {
        if (session()->has('operator')) {
            return redirect()->to('/operator/numeros');
        }
        if (session()->has('client_num')) {
            return redirect()->to('/client');
        }
        return view('auth/login');
    }

    public function authentifier()
    {
        $valeur = trim((string) $this->request->getPost('credential'));
        if (! preg_match('/^[0-9]{10}$/', $valeur)) {
            return redirect()->back()->withInput()->with('error', 'Saisissez exactement 10 chiffres.');
        }

        // Les clés sont testées avant les numéros afin de lever toute ambiguïté.
        $cle = $this->serviceMobile->chercherCle($valeur);
        if ($cle !== null) {
            session()->regenerate();
            session()->remove(['client_num', 'client_operator']);
            session()->set(['operator' => $cle['operateur'], 'role' => 'operator']);
            return redirect()->to('/operator/numeros');
        }

        $operateur = $this->serviceMobile->operateurDuNumero($valeur);
        if ($operateur === null) {
            return redirect()->back()->withInput()->with('error', 'Préfixe téléphonique inconnu.');
        }

        if (! $this->serviceMobile->creerNumeroSiAbsent($valeur)) {
            return redirect()->back()->withInput()->with('error', 'Impossible de créer ce numéro.');
        }

        session()->regenerate();
        session()->remove('operator');
        session()->set([
            'client_num' => $valeur,
            'client_operator' => $operateur,
            'role' => 'client',
        ]);
        return redirect()->to('/client');
    }

    public function deconnexion()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Vous êtes déconnecté.');
    }
}
