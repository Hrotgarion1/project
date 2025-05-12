<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Jetstream\Jetstream;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PlatformRulesController extends Controller
{
    /**
     * Show the platform rules for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function show(Request $request)
    {
        $rulesFile = Jetstream::localizedMarkdownPath('rules.md');

        return Inertia::render('PlatformRules', [
            'rules' => Str::markdown(file_get_contents($rulesFile)),
        ]);
    }
}
