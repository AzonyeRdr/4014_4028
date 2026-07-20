<?php

namespace App\Controllers;

use App\Services\MobileMoneyService;
use DomainException;

class OperatorController extends BaseController
{
    private MobileMoneyService $serviceMobile;

    public function __construct()
    {
        $this->serviceMobile = new MobileMoneyService();
    }

    public function numeros()
    {
        return view('operator/numbers', [
            'operateur' => session('operator'),
            'numeros' => $this->serviceMobile->numerosDeOperateur((string) session('operator')),
            'paginateur' => $this->serviceMobile->paginateur(),
        ]);
    }

    public function historique(string $numero)
    {
        if (! $this->possedeNumero($numero)) {
            return redirect()->to('/operator/numeros')->with('error', 'Ce numéro n’appartient pas à votre opérateur.');
        }

        $historique = $this->serviceMobile->historique($numero);
        return view('history/index', [
            'numero' => $numero,
            'transactionsSimples' => $historique['transactionsSimples'],
            'paginateurTransactions' => $this->serviceMobile->paginateur(),
            'transferts' => $historique['transferts'],
            'paginateurTransferts' => $this->serviceMobile->paginateur(),
            'urlRetour' => '/operator/numeros',
        ]);
    }

    public function gains()
    {
        $tableauGains = $this->serviceMobile->tableauGainsOperateur((string) session('operator'));
        return view('operator/gains', [
            'operateur' => session('operator'),
            'tableauGains' => $tableauGains,
            'gainTotal' => array_sum(array_column($tableauGains, 'montant')),
            'baremesFrais' => $this->serviceMobile->baremesFrais(),
        ]);
    }

    public function detailsGains(string $operateurSource)
    {
        $operateurSource = rawurldecode($operateurSource);
        try {
            $details = $this->serviceMobile->detailsGainsOperateur(
                (string) session('operator'),
                $operateurSource
            );
        } catch (DomainException $exception) {
            return redirect()->to('/operator/gains')->with('error', $exception->getMessage());
        }

        return view('operator/gain_details', [
            'operateur' => session('operator'),
            'operateurSource' => $operateurSource,
            'details' => $details,
            'paginateur' => $this->serviceMobile->paginateur(),
            'estPropreOperateur' => $operateurSource === session('operator'),
        ]);
    }

    public function ajouterBareme()
    {
        try {
            $valeurs = $this->lireBareme();
            $this->serviceMobile->ajouterBaremeFrais($valeurs['minimum'], $valeurs['maximum'], $valeurs['frais']);
            return redirect()->to('/operator/gains')->with('success', 'Plage de frais ajoutée.');
        } catch (DomainException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function modifierBareme(int $id)
    {
        try {
            $valeurs = $this->lireBareme();
            if (! $this->serviceMobile->modifierBaremeFrais($id, $valeurs['minimum'], $valeurs['maximum'], $valeurs['frais'])) {
                throw new DomainException('Barème introuvable.');
            }
            return redirect()->to('/operator/gains')->with('success', 'Plage de frais modifiée.');
        } catch (DomainException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function supprimerBareme(int $id)
    {
        if (! $this->serviceMobile->supprimerBaremeFrais($id)) {
            return redirect()->back()->with('error', 'Barème introuvable.');
        }
        return redirect()->to('/operator/gains')->with('success', 'Plage de frais supprimée.');
    }

    private function lireBareme(): array
    {
        $champs = [
            'minimum' => $this->request->getPost('montantMin'),
            'maximum' => $this->request->getPost('montantMax'),
            'frais' => $this->request->getPost('montantFrais'),
        ];
        foreach ($champs as $nom => $valeur) {
            $valeurNormalisee = str_replace(',', '.', trim((string) $valeur));
            if ($valeurNormalisee === '' || ! is_numeric($valeurNormalisee)) {
                throw new DomainException('Tous les montants de la plage doivent être numériques.');
            }
            $champs[$nom] = round((float) $valeurNormalisee, 2);
        }
        return $champs;
    }

    private function possedeNumero(string $numero): bool
    {
        return $this->serviceMobile->numeroAppartientAOperateur($numero, (string) session('operator'));
    }
}
