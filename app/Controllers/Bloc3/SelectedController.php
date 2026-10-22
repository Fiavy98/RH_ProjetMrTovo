<?php

namespace App\Controllers\Bloc3;

use App\Controllers\BaseController;
use App\Models\CandidatureModel;

class SelectedController extends BaseController
{
    public function index()
    {
        $model = new CandidatureModel();

          $candidatures = $model->findRetenuesAvecDetails();

        return view('bloc3/selected', [
            'candidatures' => $candidatures,
        ]);
    }
}