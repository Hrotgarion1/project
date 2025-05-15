<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use function App\Helpers\mapRole;
use function App\Helpers\mapRoleToType;
use Illuminate\Support\Facades\Log;

class IdentityPanelController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Identity::class);

        // Redirigir a la ruta de IdentityRequestController@index
        return redirect()->route('admin.identities.index', [
            'search_handled' => $request->input('search_handled'),
            'search_all' => $request->input('search_all'),
            'status_handled' => $request->input('status_handled'),
            'status_all' => $request->input('status_all'),
        ]);
    }
}
