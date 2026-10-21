<?php

namespace App\Controllers\Bloc4;

use App\Controllers\BlocPageController;
use App\Services\BlocPageService;
use App\Services\Bloc4\Bloc4PageService;

class Bloc4Controller extends BlocPageController
{
    protected int $bloc = 4;
    protected function pages(): BlocPageService { return new Bloc4PageService(); }
}
