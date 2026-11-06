<?php

namespace App\Controllers\Bloc5;

use App\Services\Bloc5\AuditService;

class AuditController extends Bloc5PageController
{
    public function index(): string
    {
        return $this->render((new AuditService())->page(), 'audit');
    }
}
