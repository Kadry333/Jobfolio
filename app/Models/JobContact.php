<?php

namespace App\Models;

use App\Enums\JobContactStatus;
use Database\Factories\JobContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $job_id
 * @property string $name
 * @property string|null $email
 * @property string|null $position
 * @property JobContactStatus $status
 * @property string|null $notes
 * @property string|null $linkedin_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'job_id',
    'name',
    'email',
    'position',
    'status',
    'notes',
    'linkedin_url',
])]
class JobContact extends Model
{
    /** @use HasFactory<JobContactFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_contacts';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => JobContactStatus::class,
        ];
    }

    /**
     * Get the tracked job that owns the contact.
     *
     * @return BelongsTo<TrackedJob, $this>
     */
    public function trackedJob(): BelongsTo
    {
        return $this->belongsTo(TrackedJob::class, 'job_id');
    }
}
