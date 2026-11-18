<?php

namespace App\Models;

use App\Enums\TrackedJobStatus;
use Database\Factories\TrackedJobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $jobTitle
 * @property string $companyName
 * @property string|null $jobURL
 * @property TrackedJobStatus $status
 * @property int|null $cv_id
 * @property string|null $notes
 * @property Carbon|null $appliedAt
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'jobTitle',
    'companyName',
    'jobURL',
    'status',
    'cv_id',
    'notes',
    'appliedAt',
])]
class TrackedJob extends Model
{
    /** @use HasFactory<TrackedJobFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracked_jobs';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appliedAt' => 'datetime',
            'status' => TrackedJobStatus::class,
        ];
    }

    /**
     * Get the user that owns this job application.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the CV linked to this job application.
     *
     * @return BelongsTo<Cv, $this>
     */
    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class, 'cv_id');
    }

    /**
     * Get contacts associated with this job.
     *
     * @return HasMany<JobContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(JobContact::class, 'job_id');
    }

    /**
     * Get the reminder for this job application.
     *
     * @return HasOne<JobReminder, $this>
     */
    public function reminder(): HasOne
    {
        return $this->hasOne(JobReminder::class, 'job_id');
    }
}
