<?php

namespace App\Services;

use App\DTOs\JobContactData;
use App\Models\JobContact;
use App\Models\TrackedJob;
use App\Models\User;
use App\Repositories\Contracts\JobContactRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class JobContactService
{
    public function __construct(
        protected JobContactRepositoryInterface $jobContactRepository,
    ) {}

    /**
     * Get all contacts for a user's job.
     *
     * @return Collection<int, JobContact>
     */
    public function getForJob(User $user, TrackedJob $job): Collection
    {
        abort_unless($job->user_id === $user->id, 403);

        return $this->jobContactRepository->getForJob($job->id);
    }

    /**
     * Create a new contact for a job.
     */
    public function createForJob(User $user, TrackedJob $job, JobContactData $dto): JobContact
    {
        abort_unless($job->user_id === $user->id, 403);

        return $this->jobContactRepository->createForJob($job->id, $dto->toArray());
    }

    /**
     * Update an existing contact.
     */
    public function update(User $user, JobContact $contact, JobContactData $dto): JobContact
    {
        abort_unless($contact->trackedJob->user_id === $user->id, 403);

        $this->jobContactRepository->update($contact, $dto->toArray());

        return $contact->refresh();
    }

    /**
     * Delete an existing contact.
     */
    public function delete(User $user, JobContact $contact): void
    {
        abort_unless($contact->trackedJob->user_id === $user->id, 403);

        $this->jobContactRepository->delete($contact);
    }
}
