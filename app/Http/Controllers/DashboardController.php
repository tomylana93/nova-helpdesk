<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardMetrics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, GetDashboardMetrics $getDashboardMetrics): Response
    {
        $user = $request->user();
        abort_if($user === null, 401);

        return Inertia::render('Dashboard', $getDashboardMetrics->handle($user));
    }
}
