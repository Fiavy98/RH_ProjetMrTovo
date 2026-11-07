# SIRH Bloc 6 : Description Détaillée des Écrans (Temps et Pointage)

Ce document décrit les 3 écrans du **Bloc 6 — Temps et pointage** dans le SIRH :

| Bloc | URL | Description |
|---|---|---|
| 6 — Temps et pointage | `/bloc6/horaires` | Horaires et régimes de travail |
| 6 — Temps et pointage | `/bloc6/planning` | Planning par salarié et période |
| 6 — Temps et pointage | `/bloc6/pointages` | Entrées, sorties, retards et heures supplémentaires |

---

## 1. Horaires et Régimes de Travail (`/bloc6/horaires`)

* **Rôle cible :** RH, Administrateur.
* **Finalité :** Définir et maintenir le catalogue des régimes de travail types applicables dans l'entreprise.

### Éléments d'interface
1. **Bandeau d'indicateurs (KPIs) :**
   - *Régimes enregistrés* : Nombre total de régimes existants.
   - *Régimes actifs* : Nombre de régimes utilisables pour le planning.
   - *Moyenne hebdo* : Volume moyen d'heures par semaine.

2. **Tableau des régimes (`horaire_travail`) :**
   - `Code` : Identifiant (ex: `STANDARD_40H`, `EQUIPE_MATIN`).
   - `Libellé` : Nom lisible avec description éventuelle en sous-texte.
   - `Plage horaire` : Heure de début et heure de fin (ex: `08:00 – 17:00`).
   - `Pause` : Durée de la pause (ex: `60 min`).
   - `Durée jour` : Heures nettes travaillées (ex: `8.00 h / jour`).
   - `Durée hebdo` : Heures hebdomadaires (ex: `40.00 h / sem.`).
   - `Statut` : Badge `Actif` (vert) ou `Inactif` (gris).

3. **Formulaire / Modale "Nouveau régime horaire" :**
   - `code` (Texte, majuscules, obligatoire) : ex: `STANDARD_40H`.
   - `libelle` (Texte, obligatoire) : ex: *Temps plein standard 08h00 - 17h00*.
   - `heure_debut` (Heure, obligatoire) : ex: `08:00`.
   - `heure_fin` (Heure, obligatoire) : ex: `17:00`.
   - `pause_minutes` (Entier, obligatoire) : ex: `60`.
   - `actif` (Sélection) : Actif / Inactif.
   - `description` (Texte, optionnel).

---

## 2. Planning par Salarié et Période (`/bloc6/planning`)

* **Rôle cible :** RH, Administrateur, Manager (pour son équipe).
* **Finalité :** Planifier les présences prévues par collaborateur et par jour sur une semaine donnée.

### Éléments d'interface
1. **Navigation et filtres :**
   - *Sélecteur de semaine* : Boutons `« Semaine précédente`, `Semaine courante`, `Semaine suivante »` et dates de la semaine affichée (Lundi au Dimanche).
   - *Filtre département* : Filtrer par service (Informatique, Finance, RH, etc.).
   - *Recherche salarié* : Recherche textuelle par nom ou matricule.
   - *Bouton d'action rapide* : `＋ Affecter un planning`.

2. **Grille hebdomadaire (Matrice salariés / jours) :**
   - **Lignes :** Chaque salarié actif (Matricule, Nom, Prénom, Département).
   - **Colonnes :**
     - Salarié
     - Lundi, Mardi, Mercredi, Jeudi, Vendredi, Samedi, Dimanche (avec nom du jour et quantième).
     - Total hebdomadaire (somme des heures prévues de la semaine).
   - **Cellules :**
     - Si jour travaillé planifié : Horaires prévus (ex: `08:00 – 17:00`) et durée (ex: `8.0 h`).
     - Si jour de repos : Badge `Repos`.
     - Si non encore planifié : `—`.

3. **Modale d'affectation de planning :**
   - `employe_id` : Sélection du salarié.
   - `horaire_id` : Sélection du régime horaire à appliquer.
   - `date_debut` : Date de début de la période à planifier.
   - `date_fin` : Date de fin de la période à planifier.
   - `exclure_weekend` : Case à cocher pour configurer automatiquement samedi et dimanche en repos.

---

## 3. Entrées, Sorties, Retards et Heures Supplémentaires (`/bloc6/pointages`)

* **Rôle cible :** RH, Administrateur, Manager.
* **Finalité :** Suivre les présences réelles, identifier les retards et comptabiliser les heures supplémentaires.

### Éléments d'interface
1. **Bandeau KPIs du jour :**
   - *Pointages du jour* : Nombre total d'enregistrements pour la date.
   - *Retards détectés* : Nombre de salariés arrivés après l'heure prévue du planning.
   - *Heures supplémentaires* : Volume total d'heures sup de la journée.
   - *Pointages incomplets* : Salariés ayant pointé l'entrée mais sans sortie.

2. **Filtres :**
   - *Date* : Sélecteur de date (par défaut : date du jour).
   - *Département* : Filtre par service.
   - *Statut pointage* : Tous, Salariés en retard, Heures supplémentaires, Sortie manquante.
   - *Recherche salarié* : Nom ou matricule.

3. **Tableau des pointages :**
   - `Salarié` : Nom, prénom, matricule et département.
   - `Date` : Date du pointage (format `jj/mm/aaaa`).
   - `Arrivée (Retard)` : Heure d'entrée avec badge vert si à l'heure, ou rouge avec retard en minutes (`+X min`) si arrivée après l'heure prévue.
   - `Départ` : Heure de sortie réelle ou badge orange `En attente`.
   - `Pause` : Durée déduite (en minutes).
   - `Temps réel` : Durée effective calculée (`(sortie - entree) - pause`).
   - `Heures sup` : Durée au-delà des heures prévues au planning (badge bleu).
   - `Statut` : `Validé` (vert) ou `Corrigé` (orange en cas de régularisation manuelle).
   - `Action` : Bouton `Régulariser` (pré-remplit la modale de correction).

4. **Modale "Saisie / Régularisation de pointage" :**
   - `employe_id` : Sélection du collaborateur.
   - `date_pointage` : Date concernée.
   - `heure_entree` : Heure d'arrivée réelle (`HH:MM`).
   - `heure_sortie` : Heure de départ réelle (`HH:MM`).
   - `pause_minutes` : Pause en minutes.
   - `motif_correction` : Motif obligatoire en cas de régularisation manuelle par la RH.
