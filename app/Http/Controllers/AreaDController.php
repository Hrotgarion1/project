<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaD;

class AreaDController extends AbstractAreaController
{
    protected $modelClass = AreaD::class;
    protected $areaName = 'D';
    protected $routePrefix = 'skyfall.area-d';
}
