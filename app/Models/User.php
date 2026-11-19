<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property int|null $default_cv_id
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'firstName', 'lastName', 'email', 'password', 'default_cv_id', 'email_verified_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'name',
    ];

    /**
     * Get and set the user's full name.
     *
     * @return Attribute<string, string>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => trim(($attributes['firstName'] ?? '').' '.($attributes['lastName'] ?? '')),
            set: function (?string $value) {
                if ($value === null) {
                    return [];
                }
                $parts = explode(' ', trim($value), 2);

                return [
                    'firstName' => $parts[0],
                    'lastName' => $parts[1] ?? '',
                ];
            }
        );
    }

    /**
     * Get all CVs belonging to the user.
     *
     * @return HasMany<Cv, $this>
     */
    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class);
    }

    /**
     * Get the user's default CV.
     *
     * @return BelongsTo<Cv, $this>
     */
    public function defaultCv(): BelongsTo
    {
        return $this->belongsTo(Cv::class, 'default_cv_id');
    }

    /**
     * Get all tracked jobs belonging to the user.
     *
     * @return HasMany<TrackedJob, $this>
     */
    public function trackedJobs(): HasMany
    {
        return $this->hasMany(TrackedJob::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            /* @chisel-2fa */
            'two_factor_confirmed_at' => 'datetime',
            /* @end-chisel-2fa */
        ];
    }
}
