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

## Côté opérateur et client
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