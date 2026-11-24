<?php

namespace App\Http\Requests\TrackedJobs;

use App\Enums\TrackedJobStatus;
use App\Models\TrackedJob;
use App\Models\User;
use App\Rules\UrlOrWhatsApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrackedJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var TrackedJob|null $trackedJob */
        $trackedJob = $this->route('trackedJob') ?? $this->route('job');
        /** @var User|null $user */
        $user = $this->user();

        return $user !== null && $trackedJob !== null && $trackedJob->user_id === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'jobTitle' => ['sometimes', 'required', 'string', 'max:255'],
            'companyName' => ['sometimes', 'required', 'string', 'max:255'],
            'jobURL' => ['nullable', new UrlOrWhatsApp, 'max:2048'],
            'status' => ['sometimes', 'required', Rule::enum(TrackedJobStatus::class)],
            'cv_id' => [
                'nullable',
                Rule::exists('cvs', 'id')->where('user_id', $user->id),
            ],
            'notes' => ['nullable', 'string'],
            'appliedAt' => ['nullable', 'date'],
        ];
    }
}
