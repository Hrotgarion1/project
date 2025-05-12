<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request)
       {
           $locale = $request->input('locale', config('app.locale'));
           $supportedLocales = ['ca', 'de', 'en', 'es', 'eu', 'fr', 'gl', 'it', 'pt', 'ru'];

           if (in_array($locale, $supportedLocales)) {
               session()->put('locale', $locale);
               app()->setLocale($locale);
           }

           return redirect()->back();
       }
}
