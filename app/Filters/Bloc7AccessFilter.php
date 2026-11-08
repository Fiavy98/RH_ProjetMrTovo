<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Autorisation côté serveur des parcours du Bloc 7. */
class Bloc7AccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role=(string)session()->get('role'); $path=trim(uri_string(),'/'); $method=strtoupper($request->getMethod());
        if (preg_match('#^bloc7/justificatifs/\d+/download$#',$path)) return in_array($role,['rh','salarie','manager'],true) ? null : $this->denied();
        if ($role==='admin') return null;
        $rhOnly=['bloc7/soldes/droits','bloc7/types-conges','bloc7/jours-feries','bloc7/absences','bloc7/comptes-salaries'];
        if (in_array($path,$rhOnly,true)) return $role==='rh' ? null : $this->denied();
        if ($path==='bloc7/notifications' || $path==='bloc7/notifications/lues') return in_array($role,['salarie','rh','manager','admin'],true) ? null : $this->denied();
        if (preg_match('#^bloc7/validations(?:/\d+/decision)?$#',$path)) return in_array($role,['rh','manager'],true) ? null : $this->denied();
        if ($path==='bloc7/soldes') return in_array($role,['salarie','rh','manager'],true) && $method==='GET' ? null : $this->denied();
        if (preg_match('#^bloc7/demandes(?:/nouvelle|/\d+(?:/modifier|/soumettre|/retirer)?)?$#',$path)) return in_array($role,['salarie','rh','manager'],true) ? null : $this->denied();
        return $this->denied();
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
    private function denied() { return service('response')->setStatusCode(403)->setBody('Accès interdit pour ce rôle.'); }
}
