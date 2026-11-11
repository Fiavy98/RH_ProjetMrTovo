<?php

namespace App\Controllers\Bloc7;

use App\Controllers\BaseController;
use App\Services\Bloc7\DemandeCongeService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/** Écrans et actions métier de la gestion des congés. */
class Bloc7Controller extends BaseController
{
    private DemandeCongeService $demandes;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    { parent::initController($request,$response,$logger); $this->demandes=new DemandeCongeService(); }

    public function index(){ return redirect()->to(site_url('bloc7/demandes')); }
    public function demandes(){ return $this->render('Demandes de congé','bloc7/table',['columns'=>['Référence','Employé','Type','Période','Durée','Statut',''], 'rows'=>$this->requestRows($this->demandes->liste($this->actor(),$this->role())), 'action'=>'demandes','button'=>'Nouvelle demande']); }
    public function validations(){
        $role=$this->role(); $rows=$this->demandes->liste($this->actor(),$role,'pending');
        if($role==='manager') $rows=array_values(array_filter($rows,fn($r)=>$r['statut']==='en_attente_manager' && (int)($r['manager_id']??0)===$this->actor()));
        else $rows=array_values(array_filter($rows,fn($r)=>$r['statut']==='en_attente_rh'));
        return $this->render('Demandes à valider','bloc7/table',['columns'=>['Référence','Employé','Type','Période','Durée','Étape',''], 'rows'=>$this->requestRows($rows,true),'action'=>'validations']);
    }
    public function formulaire(?int $id=null){
        $data=['title'=>$id?'Modifier la demande':'Nouvelle demande','options'=>$this->demandes->options($this->actor(),$this->role()),'item'=>null];
        if($id){$data['item']=$this->demandes->details($id,$this->actor(),$this->role()); $owned=$data['item']&&((int)$data['item']['saisi_par_employe_id']===$this->actor()||(int)$data['item']['employe_id']===$this->actor()); if(!$data['item']||!in_array($data['item']['statut'],['brouillon','a_corriger'],true)||!$owned) return $this->response->setStatusCode(404)->setBody('Demande introuvable ou non modifiable.');}
        return $this->render($data['title'],'bloc7/form',$data);
    }
    public function enregistrer(?int $id=null){
        $result=$this->demandes->enregistrer($this->request->getPost(),$this->actor(),$this->role(),$id);
        if(!$result['ok']) return redirect()->back()->withInput()->with('error',$result['message']);
        $file=$this->request->getFile('justificatif');
        if($file && $file->getError()!==UPLOAD_ERR_NO_FILE){$upload=$this->saveJustificatif((int)$result['id'],$file); if(!$upload['ok']) return redirect()->to(site_url('bloc7/demandes/'.$result['id'].'/modifier'))->with('error',$upload['message']);}
        return redirect()->to(site_url('bloc7/demandes/'.$result['id']))->with('success',$result['message']);
    }
    public function detail(int $id){$item=$this->demandes->details($id,$this->actor(),$this->role()); if(!$item)return $this->response->setStatusCode(404)->setBody('Demande introuvable.'); return $this->render('Demande '.$item['reference'],'bloc7/detail',['item'=>$item]);}
    public function soumettre(int $id){$r=$this->demandes->soumettre($id,$this->actor()); return redirect()->back()->with($r['ok']?'success':'error',$r['message']);}
    public function decider(int $id){$r=$this->demandes->decider($id,$this->actor(),$this->role(),(string)$this->request->getPost('decision'),(string)$this->request->getPost('commentaire')); return redirect()->back()->with($r['ok']?'success':'error',$r['message']);}
    public function retirer(int $id){$r=$this->demandes->retirer($id,$this->actor(),$this->role(),(string)$this->request->getPost('motif')); return redirect()->back()->with($r['ok']?'success':'error',$r['message']);}
    public function soldes(){
        $db=db_connect(); $role=$this->role();
        if($role==='salarie') (new \App\Services\Bloc7\DroitCongeService($db))->synchroniser($this->actor());
        if(in_array($role,['rh','admin'],true)) foreach($db->table('employe')->select('id')->where('actif',true)->get()->getResultArray() as $e) (new \App\Services\Bloc7\DroitCongeService($db))->synchroniser((int)$e['id']);
        $q=$db->table('solde_conge s')->select('s.*, e.matricule,e.nom,e.prenom,t.libelle AS type_libelle,c.date_debut,c.date_fin')->join('employe e','e.id=s.employe_id')->join('type_conge t','t.id=s.type_conge_id')->join('contrat c','c.id=s.contrat_id');
        if($role==='salarie')$q->where('s.employe_id',$this->actor());
        if($role==='manager')$q->where('(e.manager_id = '.$this->actor().' OR e.id = '.$this->actor().')',null,false);
        $balances=$q->orderBy('e.nom')->get()->getResultArray();
        $t=$db->table('droit_conge d')->select("d.*,e.matricule,e.nom,e.prenom,t.libelle AS type_libelle,COALESCE(SUM(CASE WHEN a.statut IN ('reservee','consommee') THEN a.quantite ELSE 0 END),0) AS alloue")
            ->join('employe e','e.id=d.employe_id')->join('type_conge t','t.id=d.type_conge_id')->join('allocation_droit_conge a','a.droit_conge_id=d.id','left')
            ->where('d.nature','acquisition')->groupBy('d.id,e.matricule,e.nom,e.prenom,t.libelle');
        if($role==='salarie')$t->where('d.employe_id',$this->actor());
        if($role==='manager')$t->where('(e.manager_id = '.$this->actor().' OR e.id = '.$this->actor().')',null,false);
        $tranches=array_map(static function($row){$expired=$row['date_expiration']<=date('Y-m-d');$row['disponible']=$expired?0:max(0,(float)$row['quantite']-(float)$row['alloue']);$row['expiree']=$expired;return $row;},$t->orderBy('d.date_acquisition','DESC')->get()->getResultArray());
        return $this->render('Droits et soldes','bloc7/balances',['balances'=>$balances,'tranches'=>$tranches]);
    }
    public function attribuerDroit(){
        return redirect()->to(site_url('bloc7/soldes'))->with('error','Les droits annuels sont acquis automatiquement à chaque mois complet.');
    }
    public function types(){return $this->render('Types de congé','bloc7/types',['types'=>db_connect()->table('type_conge')->orderBy('libelle')->get()->getResultArray(),'holidays'=>db_connect()->table('jour_ferie')->orderBy('date_ferie','DESC')->get()->getResultArray()]);}
    public function enregistrerType(){
        $p=$this->request->getPost(); $code=strtoupper(trim((string)($p['code']??''))); $label=trim((string)($p['libelle']??'')); $unit='jours_ouvres';
        if($code===''||$label==='')return redirect()->back()->withInput()->with('error','Code et libellé sont requis.');
        db_connect()->table('type_conge')->insert(['code'=>$code,'libelle'=>$label,'description'=>trim((string)($p['description']??'')),'actif'=>$this->request->getPost('actif')==='1','unite_decompte'=>$unit,'justificatif_requis'=>$this->request->getPost('justificatif_requis')==='1']); return redirect()->to(site_url('bloc7/types-conges'))->with('success','Type de congé ajouté.');
    }
    public function enregistrerJourFerie(){
        $date=(string)$this->request->getPost('date_ferie');$label=trim((string)$this->request->getPost('libelle'));if(!$this->validDate($date)||$label==='')return redirect()->back()->withInput()->with('error','Saisissez une date et un libellé de jour férié.');
        $db=db_connect();$values=['libelle'=>$label,'actif'=>$this->request->getPost('actif')==='1'];$exists=$db->table('jour_ferie')->where('date_ferie',$date)->countAllResults();if($exists)$db->table('jour_ferie')->where('date_ferie',$date)->update($values);else{$values['date_ferie']=$date;$db->table('jour_ferie')->insert($values);}return redirect()->to(site_url('bloc7/types-conges'))->with('success','Calendrier des jours fériés mis à jour.');
    }
    public function absences(){return $this->render('Absences enregistrées','bloc7/table',['columns'=>['Employé','Type','Période','Durée','État','Demande'], 'rows'=>db_connect()->table('absence a')->select('a.*,e.nom,e.prenom,t.libelle')->join('employe e','e.id=a.employe_id')->join('type_conge t','t.id=a.type_conge_id')->orderBy('date_debut','DESC')->get()->getResultArray(),'mode'=>'absence']);}
    public function notifications(){ $rows=db_connect()->table('notification_employe')->where('destinataire_employe_id',$this->actor())->orderBy('cree_le','DESC')->get()->getResultArray(); return $this->render('Mes notifications','bloc7/notifications',['notifications'=>$rows]); }
    public function marquerNotificationsLues(){ db_connect()->table('notification_employe')->where('destinataire_employe_id',$this->actor())->where('lue_le IS NULL',null,false)->update(['lue_le'=>date('Y-m-d H:i:s')]); return redirect()->back()->with('success','Notifications marquées comme lues.'); }
    public function comptesSalaries()
    {
        $db = db_connect();
        $employees = $db->table('employe e')
            ->select('e.id,e.matricule,e.nom,e.prenom,e.email,e.actif,e.manager_id,m.prenom AS manager_prenom,m.nom AS manager_nom')
            ->join('employe m', 'm.id=e.manager_id', 'left')
            ->orderBy('e.nom')->get()->getResultArray();
        $managers = $db->table('employe e')->distinct()
            ->select('e.id,e.matricule,e.nom,e.prenom')
            ->join('employe_role er', 'er.employe_id=e.id')
            ->join('role r', 'r.id=er.role_id')
            ->where('e.actif', true)->where('r.nom_role', 'Manager')
            ->orderBy('e.nom')->get()->getResultArray();

        return $this->render('Comptes salariés', 'bloc7/comptes', [
            'employees' => $employees,
            'managers' => $managers,
        ]);
    }

    public function enregistrerCompteSalarie()
    {
        $employeeId = (int) $this->request->getPost('employe_id');
        $managerInput = trim((string) $this->request->getPost('manager_id'));
        $managerId = $managerInput === '' ? null : (int) $managerInput;
        $password = (string) $this->request->getPost('mot_de_passe');
        if (strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Le mot de passe doit comporter au moins 8 caractères.');
        }

        $db = db_connect();
        $employee = $db->table('employe')->where(['id' => $employeeId, 'actif' => true])->get()->getRowArray();
        $staffRole = $db->table('role')->where('nom_role', 'Salarie')->get()->getRowArray();
        if (!$employee || !$staffRole) {
            return redirect()->back()->withInput()->with('error', 'Salarié actif ou rôle Salarié introuvable.');
        }
        if ($managerId !== null) {
            $isManager = $db->table('employe_role er')->select('er.employe_id')
                ->join('role r', 'r.id=er.role_id')
                ->join('employe e', 'e.id=er.employe_id')
                ->where('er.employe_id', $managerId)->where('e.actif', true)
                ->where('r.nom_role', 'Manager')->countAllResults() > 0;
            if ($managerId === $employeeId || !$isManager) {
                return redirect()->back()->withInput()->with('error', 'Choisissez un autre salarié actif ayant le rôle Manager, ou laissez le champ vide.');
            }
        }

        $db->transBegin();
        try {
            $db->table('employe')->where('id', $employeeId)->update([
                'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
                'manager_id' => $managerId,
            ]);
            $db->query('INSERT INTO employe_role (employe_id,role_id) VALUES (?,?) ON CONFLICT (employe_id,role_id) DO NOTHING', [$employeeId, $staffRole['id']]);
            if (!$db->transStatus()) throw new \RuntimeException('Création du compte impossible.');
            $db->transCommit();
            return redirect()->to(site_url('bloc7/comptes-salaries'))->with('success', 'Compte salarié et rattachement manager enregistrés pour ' . $employee['email'] . '.');
        } catch (Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
    public function telechargerJustificatif(int $id){$db=db_connect();$doc=$db->table('justificatif_conge j')->select('j.*,d.employe_id')->join('demande_conge d','d.id=j.demande_conge_id')->where('j.id',$id)->get()->getRowArray();if(!$doc||($this->role()!=='rh'&&(int)$doc['employe_id']!==$this->actor()))return $this->response->setStatusCode(404);$path=realpath(WRITEPATH.$doc['chemin_stockage']);$base=realpath(WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.'conges');if(!$path||!$base||!str_starts_with($path,$base.DIRECTORY_SEPARATOR)||!is_file($path))return $this->response->setStatusCode(404);return $this->response->download($path,null)->setFileName($doc['nom_fichier']);}

    private function saveJustificatif(int $id,$file):array{
        if(!$file->isValid()||$file->hasMoved())return ['ok'=>false,'message'=>'Fichier justificatif invalide.'];
        if(!in_array($file->getMimeType(),['application/pdf','image/jpeg','image/png'],true)||$file->getSize()>8*1024*1024)return ['ok'=>false,'message'=>'Formats admis : PDF, JPEG ou PNG, 8 Mo maximum.'];
        $dir=WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.'conges'; if(!is_dir($dir)&&!mkdir($dir,0770,true)&&!is_dir($dir))return ['ok'=>false,'message'=>'Stockage privé indisponible.']; $name=$file->getRandomName();
        try{$file->move($dir,$name); db_connect()->table('justificatif_conge')->insert(['demande_conge_id'=>$id,'ajoute_par_employe_id'=>$this->actor(),'nom_fichier'=>$file->getClientName(),'chemin_stockage'=>'uploads/conges/'.$name,'type_mime'=>$file->getMimeType(),'taille_octets'=>$file->getSize()]); return ['ok'=>true];}catch(Throwable $e){$path=$dir.DIRECTORY_SEPARATOR.$name;if(is_file($path))unlink($path);return ['ok'=>false,'message'=>$e->getMessage()];}
    }
    private function render(string $title,string $view,array $data=[]){return view($view,array_merge($data,['pageTitle'=>$title]));}
    private function actor():int{return (int)session()->get('auth_employee_id');}
    private function role():string{return (string)session()->get('role');}
    private function validDate(string $d):bool{$x=\DateTime::createFromFormat('Y-m-d',$d);return $x&&$x->format('Y-m-d')===$d;}
    private function requestRows(array $items,bool $validation=false):array{return array_map(static function($r)use($validation){$url=site_url('bloc7/demandes/'.(int)$r['id']);return [$r['reference'],$r['matricule'].' · '.$r['prenom'].' '.$r['nom'],$r['type_libelle'],ui_date($r['date_debut']).' – '.ui_date($r['date_fin']),$r['duree_demandee'].' '.$r['unite_decompte'],$validation?ui_status_label($r['statut']):ui_status_label($r['statut']),ui_raw('<a class="btn small" href="'.esc($url,'attr').'">Ouvrir</a>')];},$items);}
}
