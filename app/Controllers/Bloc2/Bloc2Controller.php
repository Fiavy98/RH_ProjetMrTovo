<?php

namespace App\Controllers\Bloc2;

use App\Controllers\BlocPageController;
use App\Services\BlocPageService;
use App\Services\Bloc2\Bloc2PageService;

class Bloc2Controller extends BlocPageController
{
    protected int $bloc = 2;
    protected function pages(): BlocPageService { return new Bloc2PageService(); }
}
