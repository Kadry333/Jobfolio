<?php

namespace App\Services;

use App\DTOs\CvUploadData;
use App\DTOs\TrackedJobData;
use App\DTOs\UpdateTrackedJobData;
use App\Models\TrackedJob;
use App\Models\User;
use App\Repositories\Contracts\JobContactRepositoryInterface;
use App\Repositories\Contracts\TrackedJobRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TrackedJobService
{
    public function __construct(
        protected TrackedJobRepositoryInterface $trackedJobRepository,
        protected JobContactRepositoryInterface $jobContactRepository,
        protected CvService $cvService,
    ) {}

    /**
     * Get all tracked jobs for a user with optional filtering.
     *
     * @return Collection<int, TrackedJob>
     */
    public function getAllForUser(User $user, ?string $status = null, ?string $search = null): Collection
    {
        return $this->trackedJobRepository->getAllForUser($user->id, $status, $search);
    }

    /**
     * Get paginated tracked jobs for a user with optional filtering.
     *
     * @return LengthAwarePaginator<int, TrackedJob>
     */
    public function getPaginatedForUser(User $user, int $perPage = 10, ?string $status = null, ?string $search = null): LengthAwarePaginator
    {
        return $this->trackedJobRepository->getPaginatedForUser($user->id, $perPage, $status, $search);
    }

    /**
     * Load a single job for a user with its relationships.
     */
    public function getForUser(User $user, TrackedJob $job): TrackedJob
    {
        abort_unless($job->user_id === $user->id, 403);

        return $job->load(['cv', 'contacts', 'reminder']);
    }

    /**
     * Create a new tracked job, handling CV resolution and inline uploads.
     */
    public function create(User $user, TrackedJobData $dto): TrackedJob
    {
        return DB::transaction(function () use ($user, $dto) {
            $cvId = null;

            if ($dto->cvSelection === 'new' && $dto->newCvFile !== null) {
                $cvData = new CvUploadData(
                    file: $dto->newCvFile,
                    name: $dto->newCvName,
                    isDefault: $dto->makeDefault,
                );

                $newCv = $this->cvService->uploadForUser($user, $cvData);
                $cvId = $newCv->id;
            } elseif ($dto->cvSelection === 'default') {
                $cvId = $user->default_cv_id;
            } elseif ($dto->cvSelection === 'existing') {
                $cvId = $dto->cvId;
            } elseif ($dto->cvSelection === 'none') {
                $cvId = null;
            } else {
                $cvId = $dto->cvId ?? $user->default_cv_id;
            }

            $job = $this->trackedJobRepository->createForUser($user->id, [
                'jobTitle' => $dto->jobTitle,
                'companyName' => $dto->companyName,
                'jobURL' => $dto->jobURL,
                'status' => $dto->status,
                'cv_id' => $cvId,
                'notes' => $dto->notes,
                'appliedAt' => $dto->appliedAt,
            ]);

            foreach ($dto->contacts as $contactDto) {
                $this->jobContactRepository->createForJob($job->id, $contactDto->toArray());
            }

            return $job;
        });
    }

    /**
     * Update an existing tracked job.
     */
    public function update(User $user, TrackedJob $job, UpdateTrackedJobData $dto): TrackedJob
    {
        abort_unless($job->user_id === $user->id, 403);

        return DB::transaction(function () use ($job, $dto) {
            $this->trackedJobRepository->update($job, $dto->toArray());

            if ($dto->contacts !== null) {
                $existingContactIds = $job->contacts()->pluck('id')->all();
                $keptContactIds = [];

                foreach ($dto->contacts as $contactDto) {
                    if ($contactDto->id && in_array($contactDto->id, $existingContactIds, true)) {
                        $contact = $this->jobContactRepository->findById($contactDto->id);
                        if ($contact && $contact->job_id === $job->id) {
                            $this->jobContactRepository->update($contact, $contactDto->toArray());
                            $keptContactIds[] = $contact->id;
                        }
                    } else {
                        $newContact = $this->jobContactRepository->createForJob($job->id, $contactDto->toArray());
                        $keptContactIds[] = $newContact->id;
                    }
                }

                $toDelete = array_diff($existingContactIds, $keptContactIds);
                foreach ($toDelete as $id) {
                    $contact = $this->jobContactRepository->findById($id);
                    if ($contact && $contact->job_id === $job->id) {
                        $this->jobContactRepository->delete($contact);
                    }
                }
            }

            return $job->refresh();
        });
    }

    /**
     * Delete a tracked job.
     */
    public function delete(User $user, TrackedJob $job): void
    {
        abort_unless($job->user_id === $user->id, 403);

        $this->trackedJobRepository->delete($job);
    }
}
