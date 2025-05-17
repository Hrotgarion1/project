<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaG;

class AreaGController extends AbstractAreaController
{
    protected $modelClass = AreaG::class;
    protected $areaName = 'G';
    protected $routePrefix = 'skyfall.area-g';
}
