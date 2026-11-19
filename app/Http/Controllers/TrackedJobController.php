<?php

namespace App\Http\Controllers;

use App\DTOs\TrackedJobData;
use App\DTOs\UpdateTrackedJobData;
use App\Enums\JobContactStatus;
use App\Enums\TrackedJobStatus;
use App\Http\Requests\TrackedJobs\StoreTrackedJobRequest;
use App\Http\Requests\TrackedJobs\UpdateTrackedJobRequest;
use App\Models\TrackedJob;
use App\Models\User;
use App\Services\CvService;
use App\Services\TrackedJobService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrackedJobController extends Controller
{
    public function __construct(
        protected TrackedJobService $trackedJobService,
        protected CvService $cvService,
    ) {}

    /**
     * Display a listing of all tracked jobs.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $status = $request->string('status')->toString() ?: null;
        $search = $request->string('search')->toString() ?: null;

        $jobs = $this->trackedJobService->getPaginatedForUser($user, 10, $status, $search);

        return Inertia::render('jobs/Index', [
            'jobs' => $jobs,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'statuses' => TrackedJobStatus::values(),
        ]);
    }

    /**
     * Show the form for creating a new tracked job.
     */
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $cvs = $this->cvService->getForUser($user);

        return Inertia::render('jobs/Create', [
            'cvs' => $cvs,
            'defaultCvId' => $user->default_cv_id,
            'statuses' => TrackedJobStatus::values(),
            'contactStatuses' => JobContactStatus::values(),
        ]);
    }

    /**
     * Store a newly created tracked job in storage.
     */
    public function store(StoreTrackedJobRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = TrackedJobData::fromRequest($request);

        $job = $this->trackedJobService->create($user, $dto);

        return redirect()->route('jobs.index')->with('success', 'Job application added successfully.');
    }

    /**
     * Display the specified tracked job.
     */
    public function show(Request $request, TrackedJob $trackedJob): Response
    {
        /** @var User $user */
        $user = $request->user();

        $job = $this->trackedJobService->getForUser($user, $trackedJob);

        return Inertia::render('jobs/Show', [
            'job' => $job,
            'statuses' => TrackedJobStatus::values(),
            'contactStatuses' => JobContactStatus::values(),
        ]);
    }

    /**
     * Show the form for editing the specified tracked job.
     */
    public function edit(Request $request, TrackedJob $trackedJob): Response
    {
        /** @var User $user */
        $user = $request->user();

        $job = $this->trackedJobService->getForUser($user, $trackedJob);
        $cvs = $this->cvService->getForUser($user);

        return Inertia::render('jobs/Edit', [
            'job' => $job,
            'cvs' => $cvs,
            'statuses' => TrackedJobStatus::values(),
            'contactStatuses' => JobContactStatus::values(),
        ]);
    }

    /**
     * Update the specified tracked job in storage.
     */
    public function update(UpdateTrackedJobRequest $request, TrackedJob $trackedJob): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = UpdateTrackedJobData::fromRequest($request);

        $this->trackedJobService->update($user, $trackedJob, $dto);

        return redirect()->route('jobs.show', $trackedJob)->with('success', 'Job application updated.');
    }

    /**
     * Remove the specified tracked job from storage.
     */
    public function destroy(Request $request, TrackedJob $trackedJob): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->trackedJobService->delete($user, $trackedJob);

        return redirect()->route('jobs.index')->with('success', 'Job application deleted.');
    }
}
