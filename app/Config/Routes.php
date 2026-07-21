<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', static function () {
    if (session()->has('operator')) {
        return redirect()->to('/operator/numeros');
    }
    if (session()->has('client_num')) {
        return redirect()->to('/client');
    }
    return redirect()->to('/login');
});

$routes->get('login', 'AuthController::connexion');
$routes->post('login', 'AuthController::authentifier');
$routes->post('logout', 'AuthController::deconnexion');

$routes->group('client', ['filter' => 'clientAuth'], static function ($routes) {
    $routes->get('/', 'ClientController::tableauDeBord');
    $routes->get('historique', 'ClientController::historique');
    $routes->post('depot', 'ClientController::deposer');
    $routes->post('retrait', 'ClientController::retirer');
    $routes->post('transfert', 'ClientController::transferer');
    $routes->post('epargne', 'ClientController::epargner');
});

$routes->group('operator', ['filter' => 'operatorAuth'], static function ($routes) {
    $routes->get('numeros', 'OperatorController::numeros');
    $routes->get('numeros/(:num)/historique', 'OperatorController::historique/$1');
    $routes->get('gains', 'OperatorController::gains');
    $routes->get('gains/details/(:segment)', 'OperatorController::detailsGains/$1');
    $routes->post('frais', 'OperatorController::ajouterBareme');
    $routes->post('frais/(:num)', 'OperatorController::modifierBareme/$1');
    $routes->post('frais/(:num)/supprimer', 'OperatorController::supprimerBareme/$1');
    $routes->post('promotion/(:num)', 'OperatorController::modifierPromotion/$1');
});
