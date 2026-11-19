<?php

namespace App\Services;

use App\DTOs\CvUploadData;
use App\Models\Cv;
use App\Models\User;
use App\Repositories\Contracts\CvRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CvService
{
    public function __construct(
        protected CvRepositoryInterface $cvRepository,
        protected UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Upload and create a CV for the user.
     */
    public function uploadForUser(User $user, CvUploadData $dto): Cv
    {
        $path = $dto->file->store('cvs', 'public');
        $name = $dto->name ?: pathinfo($dto->file->getClientOriginalName(), PATHINFO_FILENAME);

        $cv = $this->cvRepository->createForUser($user->id, [
            'name' => $name,
            'file_path' => $path,
        ]);

        if ($dto->isDefault || is_null($user->default_cv_id)) {
            $this->userRepository->setDefaultCv($user, $cv->id);
        }

        return $cv;
    }

    /**
     * Set a CV as the user's default.
     */
    public function setDefault(User $user, Cv $cv): void
    {
        abort_unless($cv->user_id === $user->id, 403);

        $this->userRepository->setDefaultCv($user, $cv->id);
    }

    /**
     * Delete a CV, its storage file, and reset default if needed.
     */
    public function delete(User $user, Cv $cv): void
    {
        abort_unless($cv->user_id === $user->id, 403);

        if ($cv->file_path && Storage::disk('public')->exists($cv->file_path)) {
            Storage::disk('public')->delete($cv->file_path);
        }

        if ($user->default_cv_id === $cv->id) {
            $this->userRepository->setDefaultCv($user, null);
        }

        $this->cvRepository->delete($cv);
    }

    /**
     * Get all CVs belonging to the user.
     *
     * @return Collection<int, Cv>
     */
    public function getForUser(User $user): Collection
    {
        return $this->cvRepository->getForUser($user->id);
    }
}
