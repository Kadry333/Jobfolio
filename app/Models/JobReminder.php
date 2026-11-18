<?php

namespace App\Models;

use Database\Factories\JobReminderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $job_id
 * @property Carbon $scheduled_at
 * @property Carbon|null $sent_at
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'job_id',
    'scheduled_at',
    'sent_at',
    'status',
])]
class JobReminder extends Model
{
    /** @use HasFactory<JobReminderFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_reminders';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the tracked job that owns the reminder.
     *
     * @return BelongsTo<TrackedJob, $this>
     */
    public function trackedJob(): BelongsTo
    {
        return $this->belongsTo(TrackedJob::class, 'job_id');
    }
}
