# Suivi Bloc 4 : Contrat et onboarding (responsable : [ton prénom])
Dernière mise à jour : 2026-10-20 | Phase en cours : relecture avant développement | État : en cours

## Fait et validé
- Analyse fonctionnelle, modèle conceptuel, workflow, écrans (2026-10-20)
- Traçabilité et authentification repoussées en fin de projet (2026-10-20)

## Tables / objets du bloc
- type_contrat : paramètres des contrats (CE, CDD, CDI : durée max, nombre de renouvellements)
- contrat : contrats successifs d'un salarié, renouvellement lié au précédent
- type_tache_onboarding : liste fixe des 6 tâches
- onboarding : un dossier d'intégration par salarié
- onboarding_tache : une ligne par tâche, avec statut
- document : fichiers rattachés à un salarié ou à un contrat

## J'attends des autres blocs
- Bloc 1 : tables departement, poste, employe (matricule inclus)
- Bloc 3 : un salarié créé avec son matricule après acceptation de la proposition ; table affectation corrigée

## Je fournis aux autres blocs
- Table type_contrat (codes CE, CDD, CDI) à référencer depuis proposition_embauche
- Liste des contrats d'un salarié et son statut d'onboarding

## Questions en suspens
- Poste.nombre_postes contredit « 1 poste = 1 titulaire » (bloquant : non)
- Contraintes d'unicité de l'affectation à ajouter côté Bloc 3 (bloquant : non)
- Candidats fictifs pour les données de test (bloquant : non)
- Salaire dans le contrat ou seulement dans la proposition (bloquant : non)