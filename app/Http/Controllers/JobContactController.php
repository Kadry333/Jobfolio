<?php

namespace App\Http\Controllers;

use App\DTOs\JobContactData;
use App\Http\Requests\JobContacts\StoreJobContactRequest;
use App\Http\Requests\JobContacts\UpdateJobContactRequest;
use App\Models\JobContact;
use App\Models\TrackedJob;
use App\Models\User;
use App\Services\JobContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobContactController extends Controller
{
    public function __construct(
        protected JobContactService $jobContactService,
    ) {}

    /**
     * Store a newly created contact for a tracked job.
     */
    public function store(StoreJobContactRequest $request, TrackedJob $trackedJob): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = JobContactData::fromArray($request->validated());
        $this->jobContactService->createForJob($user, $trackedJob, $dto);

        return back()->with('success', 'Contact added successfully.');
    }

    /**
     * Update the specified contact.
     */
    public function update(UpdateJobContactRequest $request, JobContact $jobContact): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = JobContactData::fromArray($request->validated());
        $this->jobContactService->update($user, $jobContact, $dto);

        return back()->with('success', 'Contact updated successfully.');
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Request $request, JobContact $jobContact): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->jobContactService->delete($user, $jobContact);

        return back()->with('success', 'Contact removed successfully.');
    }
}
