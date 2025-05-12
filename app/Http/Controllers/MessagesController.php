<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function send(Request $request)
    {
        return response()->json(['message' => 'Mensaje enviado con éxito']);
    }
}
