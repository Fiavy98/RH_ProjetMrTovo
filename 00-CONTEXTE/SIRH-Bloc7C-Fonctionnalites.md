# Bloc 7 — Partie C : fonctionnalités

## Portail salarié

- Connexion au compte lié au dossier `employe` et rôle Salarié cumulable avec Manager ou RH.
- Consultation du solde annuel par contrat : acquis, consommé, réservé, disponible et tranches avec expiration.
- Création, soumission, consultation et retrait de ses propres demandes.
- Correction et resoumission d'une demande retournée, avec historique conservé.
- Consultation des décisions, commentaires et notifications associées.

## Traitement des demandes

- Routage au manager direct ; routage aux RH si aucun manager n'est associé.
- Une décision par soumission : approbation, refus ou demande de correction, avec acteur, date et commentaire.
- Vérification des dates, chevauchements et solde déjà acquis à la date de soumission.
- Contrôle du congé annuel selon l'unité jours ouvrés ; maladie et exceptionnel ne consomment pas le solde annuel.
- Réservation/libération transactionnelle des droits et transmission de la demande approuvée au suivi d'absence.
- Notifications applicatives pour soumission, décision, correction, retrait et mise à jour du dossier.

## Acquisition et solde

- Crédit de 2,5 jours ouvrés à chaque mois complet de service, calculé aux anniversaires mensuels du contrat.
- Synchronisation idempotente via la commande `php spark conges:acquerir`, à planifier quotidiennement sur le serveur.
- Expiration individuelle de chaque tranche trois ans après son acquisition.
- Consommation FIFO des tranches non expirées.
- Pas d'acquisition anticipée pour les demandes futures ; pas de report de solde d'un contrat au suivant.
- Nouvelle synthèse de solde par contrat, avec historique conservé pour les contrats précédents.

## Fonctions de gestion RH

- Consultation et traitement des demandes adressées aux RH et supervision selon les habilitations.
- Consultation des soldes et historique des contrats/droits.
- Gestion du calendrier des jours fériés nécessaire au calcul des jours ouvrés.
- Consultation de l'historique de notifications et décisions selon les droits d'accès.

## Exploitation et limites

La commande `php spark conges:acquerir` doit être planifiée quotidiennement pour générer les tranches même si aucun salarié ne consulte son solde. Les jours fériés sont configurés par la RH. L'email n'est pas un canal de notification actif. Le paramétrage des justificatifs par type, les corrections d'une demande déjà approuvée et les demandes dont les droits expirent pendant la validation restent à cadrer.
