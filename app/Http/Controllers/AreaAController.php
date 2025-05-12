<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaA;

class AreaAController extends Controller
{
    protected $modelClass = AreaA::class;
    protected $areaName = 'A';
    protected $routePrefix = 'skyfall.area-a';
}
