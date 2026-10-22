<?php

namespace App\Controllers\Bloc3;

use App\Controllers\BlocPageController;
use App\Services\BlocPageService;
use App\Services\Bloc3\Bloc3PageService;

class Bloc3Controller extends BlocPageController
{
    protected int $bloc = 3;

    protected function pages(): BlocPageService { 
        return new Bloc3PageService(); 
    }
}


