<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div><p class="eyebrow">Congés et absences</p><h1><?= ui_role()==='salarie'?'Mon solde de congé':'Droits et soldes' ?></h1><p>Acquisitions de 2,5 jours ouvrés par mois complet. Les tranches expirées ne sont plus disponibles.</p></div></div>
<?= ui_panel('Soldes par contrat',ui_table(['Salarié','Contrat','Type','Acquis non expiré','Consommé','Réservé','Disponible'],array_map(static fn($b)=>[$b['matricule'].' · '.$b['prenom'].' '.$b['nom'],ui_date($b['date_debut']).' – '.($b['date_fin']?ui_date($b['date_fin']):'En cours'),$b['type_libelle'],$b['quantite_acquise'],$b['quantite_consommee'],$b['quantite_reservee'],$b['quantite_disponible']],$balances))) ?>
<?= ui_panel('Détail des tranches mensuelles',ui_table(['Salarié','Type','Acquise le','Expire le','Crédit','Disponible','État'],array_map(static fn($t)=>[$t['matricule'].' · '.$t['prenom'].' '.$t['nom'],$t['type_libelle'],ui_date($t['date_acquisition']),ui_date($t['date_expiration']),$t['quantite'],$t['disponible'],$t['expiree']?'Expirée':'Valide'],$tranches))) ?>
<?= $this->endSection() ?>
