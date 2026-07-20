# Todo list du projet

Lecture et analyse du sujet [4028,4014]

## Version 1 :

### Initialisation de la base
Initialisation de la base de données [4028,4014]

## Login RBAC
Un seul login , différent endpoint selon le rôle. [4014]

#### Table
```Table 
-numeros (pour le client)
-cles (pour l'opérateur)
```
#### Métier
```tdl
-CRUD numeros (Auto inscription)
```

#### Vue 
```
-Formulaire de login avec un seul input
```

## Côté opérateur et client [4028]
#### Table
```Table
-frais (les frais de retrait et de transfert, s'applique sur celui qui envoie la requête)
```

#### Métier
```tdl
-Calcul du solde pour un numero
-Calcul de l'historique des opérations avec type (dépôt, retrait et transfert)
-Calcul de l'historique des opérations avec type (dépôt, retrait et transfert)
```

## Côté opérateur [4014]
#### Métier
L'administrateur doit être en mesure de modifier,supprimer ou ajouter des barèmes de frais.

```tdl
-CRUD frais (contrainte sur ajouter , les barèmes ne peuvent pas se supperposer)
-Recherche filtrée
```

#### Vue
```vue
-Liste des numeros et de leur solde
-Historique des opérations
```

## Côté client [4028]
#### Table
```Table
-transfert
-transaction (retrait, dépôt)
```

#### Métier
```tdl
-CRUD transfert
-CRUD transaction
```

#### Vue
Chaque opération est un formulaire distinct.

```vue
-Formulaire de transfert
-Formulaire de dépôt 
-Formulaire de retrait
```

## Version 2 :

## Côté opérateur [4014]
#### Table
```Table
-commissions (pourcentage de commission inter-opérateur)
-transaction (stockage des frais et des commissions appliqués)
```

#### Métier
```tdl
-Calcul des gains pour tous les opérateurs du système
-Calcul des frais de retrait et de transfert perçus sur les numéros de l'opérateur connecté
-Calcul des gains provenant des autres opérateurs à partir des transferts entrants
-Un transfert entrant provient d'un numéro d'un opérateur étranger vers un numéro de l'opérateur connecté
-Exclusion des frais envoyés au destinataire du calcul des gains
-Somme des commissions par opérateur étranger
-Affichage de tous les transferts entrants dans les détails des gains
-Pagination des détails des gains par 10 éléments
-Absence de double comptage des frais lors d'un transfert multiple
```

#### Vue
```vue
-Tableau récapitulatif des gains listant tous les opérateurs
-Montant total des gains pour chaque opérateur
-Lien vers les détails des gains de chaque opérateur
-Liste paginée des frais de retrait et de transfert du réseau connecté
-Liste paginée de tous les transferts entrants provenant d'un opérateur étranger
```

## Côté client [4028]
#### Table
```Table
-transaction (débit global, crédits individuels, frais et commissions)
-transfert (liaison entre le débit global et chaque crédit destinataire)
```

#### Métier
```tdl
-Transfert unique ou multiple
-Ajout dynamique de plusieurs numéros destinataires
-Création automatique d'un numéro destinataire absent
-Division équitable du montant total entre les destinataires
-Calcul individuel des frais pour chaque destinataire
-Cumul des frais de transfert sur le débit global de l'expéditeur
-Option pour inclure les frais de retrait dans le montant envoyé à chaque destinataire
-Exclusion des frais envoyés au destinataire de la base de calcul des gains
-Calcul de la commission pour les transferts entre opérateurs différents
-Commission nulle pour les transferts entre numéros du même opérateur
-Création d'une transaction de crédit par destinataire
-Création d'une transaction de débit globale pour l'expéditeur
-Vérification du solde avec les montants, les frais et les commissions
-Stockage du numéro et de l'opérateur du client en session
```

#### Vue
```vue
-Formulaire dynamique de transfert multi-destinataires
-Bouton pour ajouter un numéro destinataire
-Bouton pour supprimer un numéro destinataire
-Case à cocher pour inclure les frais de retrait dans l'envoi
-Affichage du total des frais de transfert après l'opération
-Affichage de la commission et du montant total débité
```
