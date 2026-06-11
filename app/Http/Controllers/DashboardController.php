<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardData;
use App\Http\Requests\DashboardRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(DashboardRequest $request, GetDashboardData $getDashboardData): Response
    {
        $user = $request->user();
        abort_if($user === null, 401);

        return Inertia::render('Dashboard', $getDashboardData->handle($user, $request->toPeriod()));
    }
}
