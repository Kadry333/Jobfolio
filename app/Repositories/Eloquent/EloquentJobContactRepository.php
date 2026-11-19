<?php

namespace App\Repositories\Eloquent;

use App\Models\JobContact;
use App\Repositories\Contracts\JobContactRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentJobContactRepository implements JobContactRepositoryInterface
{
    /**
     * Get all contacts for a specific job.
     *
     * @return Collection<int, JobContact>
     */
    public function getForJob(int $jobId): Collection
    {
        return JobContact::query()
            ->where('job_id', $jobId)
            ->latest()
            ->get();
    }

    /**
     * Find a contact by ID.
     */
    public function findById(int $id): ?JobContact
    {
        return JobContact::query()->find($id);
    }

    /**
     * Create a new contact for a job.
     *
     * @param  array<string, mixed>  $data
     */
    public function createForJob(int $jobId, array $data): JobContact
    {
        return JobContact::query()->create([
            ...$data,
            'job_id' => $jobId,
        ]);
    }

    /**
     * Update an existing contact.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(JobContact $contact, array $data): bool
    {
        return $contact->update($data);
    }

    /**
     * Delete a contact.
     */
    public function delete(JobContact $contact): bool
    {
        return (bool) $contact->delete();
    }
}
