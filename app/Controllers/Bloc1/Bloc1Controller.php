<?php

namespace App\Controllers\Bloc1;

use App\Controllers\BlocPageController;
use App\Services\BlocPageService;
use App\Services\Bloc1\Bloc1PageService;

class Bloc1Controller extends BlocPageController
{
    protected int $bloc = 1;
    protected function pages(): BlocPageService { return new Bloc1PageService(); }
}
