<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaF;

class AreaFController extends AbstractAreaController
{
    protected $modelClass = AreaF::class;
    protected $areaName = 'F';
    protected $routePrefix = 'skyfall.area-f';
}
