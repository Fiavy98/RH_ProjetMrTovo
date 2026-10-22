# Conception d'un logiciel de gestion RH

## Le cycle d'un personnel dans une entreprise

- Besoin
- Recrutement
- Enmbauche
- Vie de salarie
- Evolution
- Rupture

## Objectif

- comprendre le processus metier et regle de gestion classique
- connaitre les types de donnees
- flux d'informations
- les ecrans necessaires

## Bloc 1 : Expression du besoin en personnel

**Informations :** poste, departement, effectif,motif, budget, date souhaitee \
**Workflow :** Manager -> RH -> Direction -> ouverture de recrutement \
**Objets metier :** departement, poste, demande recrutement, validation \
**Point de conception :** un POSTE existe independamment de l'EMPLOYE

## Bloc 2 : Recrutement

Demande validee -> offre d'emploi \
Publication de l'offre, reception de candidatures \
Preselection, organisation des entretiens et tests
Conservation des evaluations et la decision finale

**Objets :** offre d'emploi, candidat, candidature, entretien, evaluation candidat

**NB :** Rendre tracable tous les actions

## Bloc 3 : Selection et embauche

Le candidat retenu recoit une proposition d'embauche \
Apres acceptation : creation du dossier salarie et du matricule \
Creer le contrat, le poste et l'affectation \
Point de conception : **CANDIDAT != EMPLOYE** \
Une personne peut avoir plusieurs candidatures avant de devenir salariee

## Bloc 4 : Contrat et onboarding

Un salariee peut avoir plusieurs contrats successifs : CDD, renouvellement, CDI,..

- CE : Contrat d'Essai, pas plus de 6 mois, renouvelable 1 fois
- CDD : maximum 2 ans, renouvelable 1 fois
- CDI

Onboarding : contrat signe, badge, compte, materiel, formation, inscription paie \
**Objet :** contrat, affectation, onboarding, onboardingTache, document
