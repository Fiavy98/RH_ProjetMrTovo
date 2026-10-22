# Bloc 4 — Décisions fonctionnelles

Décisions communiquées par le responsable du Bloc 4 le 2026-10-22. Ce document complète l'analyse fonctionnelle, le workflow et les écrans du Bloc 4.

## 1. Contrats d'essai (CE)

- Une période de CE ne dépasse pas **6 mois**.
- Un CE peut être renouvelé **une seule fois au maximum**.
- Le renouvellement est un nouveau contrat de type CE, lié au contrat précédent.
- Le nombre de renouvellements est la règle déterminante : après un renouvellement, aucun renouvellement supplémentaire n'est autorisé, même si la durée cumulée des périodes est inférieure à 12 mois.
- Exemples : CE de 6 mois + renouvellement de 6 mois : autorisé, sans renouvellement ultérieur ; CE de 4 mois + renouvellement de 3 mois : autorisé, sans renouvellement ultérieur.

## 2. Contrats à durée déterminée (CDD)

- Une période de CDD ne dépasse pas **12 mois**.
- Un CDD peut être renouvelé **une seule fois au maximum**.
- Le renouvellement est un nouveau contrat de type CDD, lié au contrat précédent.
- Après ce renouvellement, aucun renouvellement supplémentaire n'est autorisé, quelle que soit la durée cumulée.

## 2.1 Convention de calcul des durées

- La durée est calculée en **mois calendaires**, pas en nombre fixe de jours.
- `date_fin` désigne le dernier jour couvert par le contrat (borne incluse).
- La limite de `N` mois est respectée si le lendemain de `date_fin` est inférieur ou égal à la date obtenue en ajoutant `N` mois calendaires à `date_debut`.
- Pour les dates de fin de mois, l'ajout de mois conserve le jour si le mois cible le possède ; sinon il retient le dernier jour du mois cible (ex. le 31 janvier + 1 mois = le 28 février, ou le 29 en année bissextile).
- Exemple : pour une période d'un mois commençant le 15 mars, le dernier jour autorisé est le 14 avril. Pour une période commençant le 31 janvier, le dernier jour autorisé est le 27 février (ou le 28 en année bissextile).
- La même convention s'applique à la durée maximale de chaque période CE ou CDD, renouvellement compris.

## 3. CDI et contrats successifs

- Un CDI n'a pas de date de fin.
- Les contrats de type différent restent indépendants : un CDD après un CE n'est pas le renouvellement du CE.
- Le salaire est saisi et enregistré comme une donnée du contrat. Cette décision pourra être révisée ultérieurement.

## 4. Affectation

- Un salarié ne peut occuper qu'un seul poste à la fois : **un employé = un poste actif**.
- En attendant que la fonctionnalité d'affectation du Bloc 3 soit disponible, le Bloc 4 crée une affectation de simulation en base pour pouvoir développer et démontrer son parcours.
- Cette affectation temporaire devra être raccordée à la fonctionnalité partagée du Bloc 3 lorsqu'elle sera disponible ; elle ne doit pas créer un modèle concurrent à long terme.
- Les affectations terminées sont conservées dans l'historique et clôturées plutôt que supprimées.

## 5. Onboarding

- Une fois le parcours initial de contrat terminé, un onboarding est créé pour le nouvel employé. Pour l'implémentation, « contrat terminé » signifie **contrat initial signé**, conformément au workflow du Bloc 4.
- L'onboarding comporte exactement six tâches, créées avec le statut `a_faire` :
  1. Contrat signé
  2. Badge
  3. Compte informatique
  4. Matériel
  5. Formation d'accueil
  6. Inscription à la paie
- Les six tâches sont présentées sur un seul écran. L'utilisateur peut modifier le statut de chacune, puis enregistrer l'ensemble des changements avec un bouton **Enregistrer**.
- Le statut global de l'onboarding est calculé à partir des statuts des tâches : `a_faire`, `en_cours` ou `termine`. Il passe à `termine` lorsque les six tâches sont terminées.

## 6. Gestion des rôles

- La gestion des rôles et des permissions n'est pas incluse dans cette étape du Bloc 4.
- Les droits par rôle et les restrictions d'accès seront traités ultérieurement, dans le travail commun sur l'authentification et les autorisations.

## 7. Conséquences pour le développement

- Les limites de durée et de renouvellement doivent être lues depuis le paramétrage des types de contrat, et non codées en dur dans le service métier.
- Chaque période de renouvellement est enregistrée dans une ligne de contrat distincte, liée au contrat précédent. Un contrat renouvelé ne peut pas être renouvelé à nouveau.
- Les tests fonctionnels et données de démonstration doivent couvrir les cas CE et CDD avec un renouvellement, ainsi que le refus d'un second renouvellement.
- Les scripts SQL et le paramétrage existants devront être alignés avec ces décisions, en particulier la durée maximale paramétrée du CDD (**12 mois par période**) et la règle d'un seul renouvellement.
