<?php

namespace App\Http\Requests\TrackedJobs;

use App\Enums\JobContactStatus;
use App\Enums\TrackedJobStatus;
use App\Models\User;
use App\Rules\UrlOrWhatsApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrackedJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'jobTitle' => ['required', 'string', 'max:255'],
            'companyName' => ['required', 'string', 'max:255'],
            'jobURL' => ['nullable', new UrlOrWhatsApp, 'max:2048'],
            'status' => ['required', Rule::enum(TrackedJobStatus::class)],
            'cv_selection' => ['nullable', 'string', 'in:default,existing,new,none'],
            'cv_id' => [
                'nullable',
                Rule::exists('cvs', 'id')->where('user_id', $user->id),
            ],
            'new_cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'new_cv_name' => ['nullable', 'string', 'max:255'],
            'make_default' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'appliedAt' => ['nullable', 'date'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['required', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.position' => ['nullable', 'string', 'max:255'],
            'contacts.*.status' => ['nullable', Rule::enum(JobContactStatus::class)],
            'contacts.*.notes' => ['nullable', 'string', 'max:2000'],
            'contacts.*.linkedin_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
