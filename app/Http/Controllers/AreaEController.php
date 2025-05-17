<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaE;

class AreaEController extends AbstractAreaController
{
    protected $modelClass = AreaE::class;
    protected $areaName = 'E';
    protected $routePrefix = 'skyfall.area-e';
}
