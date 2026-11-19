<?php

namespace App\Http\Requests\JobContacts;

use App\Enums\JobContactStatus;
use App\Models\TrackedJob;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();
        /** @var TrackedJob|null $job */
        $job = $this->route('trackedJob');

        return $user !== null && $job !== null && $job->user_id === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(JobContactStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'linkedin_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
