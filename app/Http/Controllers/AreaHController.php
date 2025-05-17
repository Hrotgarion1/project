<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaH;

class AreaHController extends AbstractAreaController
{
    protected $modelClass = AreaH::class;
    protected $areaName = 'H';
    protected $routePrefix = 'skyfall.area-h';
}
