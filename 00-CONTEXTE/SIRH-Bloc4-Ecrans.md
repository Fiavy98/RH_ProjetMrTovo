# SIRH Bloc 4 : Les écrans

| Écran | Utilisateur | Contenu | Règles contrôlées |
|---|---|---|---|
| Liste des contrats | RH | Filtres par type (CE, CDD, CDI), statut, salarié ; accès à la fiche | Aucune (consultation) |
| Création / modification de contrat | RH uniquement | Choix du salarié et du type, dates de début et de fin (pas de date de fin pour un CDI), date de signature | Durée ≤ durée max du type, pas de chevauchement avec un autre contrat du salarié, CDI sans date de fin |
| Fiche contrat | RH | Détail, statut, chaîne de renouvellement (contrat initial et renouvellement), documents rattachés, changement de statut | Transitions de statut autorisées uniquement |
| Renouvellement | RH uniquement | Crée un nouveau contrat lié au précédent, du même type, qui commence le lendemain de la fin du précédent | 1 seul renouvellement par contrat, un renouvellement ne se renouvelle pas, durée ≤ durée max du type |
| Documents d'un salarié / contrat | RH | Ajout et consultation des fichiers (contrat signé, pièce d'identité, etc.) | Fichier stocké sur disque, chemin en base |
| Liste des onboardings | RH | Salariés en cours d'intégration, avancement (tâches terminées sur 6) | Aucune (consultation) |
| Onboarding d'un salarié | RH, Manager | Les 6 tâches fixes (contrat signé, badge, compte, matériel, formation, inscription paie) avec responsable, échéance, statut, date de réalisation | L'onboarding est terminé quand les 6 tâches le sont ; pas d'ajout ni de suppression de tâche |
| Paramètres des contrats | Administrateur | Durée max et nombre de renouvellements par type de contrat | Valeurs positives, CDI sans durée max |
