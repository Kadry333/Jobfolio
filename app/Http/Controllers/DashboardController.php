<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    /**
     * Display the Trackly dashboard.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $data = $this->dashboardService->getDashboardData($user);

        return Inertia::render('Dashboard', $data);
    }
}
