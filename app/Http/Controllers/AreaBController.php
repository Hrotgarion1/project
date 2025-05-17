<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaB;

class AreaBController extends AbstractAreaController
{
    protected $modelClass = AreaB::class;
    protected $areaName = 'B';
    protected $routePrefix = 'skyfall.area-b';
}
