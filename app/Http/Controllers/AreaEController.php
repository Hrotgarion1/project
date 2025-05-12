<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaE;

class AreaEController extends Controller
{
    protected $modelClass = AreaE::class;
    protected $areaName = 'E';
    protected $routePrefix = 'skyfall.area-e';
}
