<?php

namespace App\Http\Controllers;

use App\DTOs\CvUploadData;
use App\Http\Requests\Onboarding\StoreOnboardingCvRequest;
use App\Models\User;
use App\Services\CvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(
        protected CvService $cvService,
    ) {}

    /**
     * Show the optional CV upload onboarding step.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // If user already has a default CV, redirect them to dashboard
        if ($user->default_cv_id) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('onboarding/CvUpload', [
            'user' => [
                'firstName' => $user->firstName,
                'name' => $user->name,
            ],
        ]);
    }

    /**
     * Handle the optional CV upload and proceed to dashboard.
     */
    public function store(StoreOnboardingCvRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = CvUploadData::fromRequest($request);

        $this->cvService->uploadForUser($user, $dto);

        return redirect()->route('dashboard')->with('success', 'Default CV saved! You are all set.');
    }
}
