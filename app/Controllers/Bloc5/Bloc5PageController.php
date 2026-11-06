<?php

namespace App\Controllers\Bloc5;

use App\Controllers\BaseController;

abstract class Bloc5PageController extends BaseController
{
    protected const BLOCK_TITLE = 'Bloc 5 · Dossier administratif du salarié';

    protected function render(array $page, string $view): string
    {
        return view('bloc5/' . $view, [
            'pageTitle' => $page['title'],
            'blockTitle' => self::BLOCK_TITLE,
            'screen' => $page,
        ]);
    }
}
