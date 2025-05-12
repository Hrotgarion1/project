<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IdentityType;
use Illuminate\Http\Request;

class IdentityTypeController extends Controller
{
    public function getRequiredDocuments($type)
    {
        $identityType = IdentityType::where('type', $type)->firstOrFail();
        return response()->json([
            'type' => $identityType->type,
            'required_documents' => $identityType->required_documents ?? [],
            'terms_url' => route('terms_conditions.show', ['type' => $identityType->type]),
        ]);
    }
}