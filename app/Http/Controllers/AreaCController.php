<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaC;

class AreaCController extends Controller
{
    protected $modelClass = AreaC::class;
    protected $areaName = 'C';
    protected $routePrefix = 'skyfall.area-c';
}