<?php

namespace App\Repositories\Contracts;

use App\Models\JobContact;
use Illuminate\Database\Eloquent\Collection;

interface JobContactRepositoryInterface
{
    /**
     * Get all contacts for a specific job.
     *
     * @return Collection<int, JobContact>
     */
    public function getForJob(int $jobId): Collection;

    /**
     * Find a contact by ID.
     */
    public function findById(int $id): ?JobContact;

    /**
     * Create a new contact for a job.
     *
     * @param  array<string, mixed>  $data
     */
    public function createForJob(int $jobId, array $data): JobContact;

    /**
     * Update an existing contact.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(JobContact $contact, array $data): bool;

    /**
     * Delete a contact.
     */
    public function delete(JobContact $contact): bool;
}
