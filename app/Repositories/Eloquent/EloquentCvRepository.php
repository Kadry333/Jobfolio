<?php

namespace App\Repositories\Eloquent;

use App\Models\Cv;
use App\Repositories\Contracts\CvRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentCvRepository implements CvRepositoryInterface
{
    public function createForUser(int $userId, array $attributes): Cv
    {
        $attributes['user_id'] = $userId;

        return Cv::create($attributes);
    }

    public function getForUser(int $userId): Collection
    {
        return Cv::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->get();
    }

    public function findByIdAndUser(int $id, int $userId): ?Cv
    {
        return Cv::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function delete(Cv $cv): bool
    {
        return (bool) $cv->delete();
    }
}
