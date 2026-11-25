<?php

namespace App\Http\Requests\Cvs;

use App\Models\User;
use App\Services\CvService;
use Illuminate\Foundation\Http\FormRequest;

class StoreCvRequest extends FormRequest
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
        /** @var CvService $cvService */
        $cvService = app(CvService::class);

        return [
            'cv_file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
                function (string $attribute, mixed $value, \Closure $fail) use ($user, $cvService) {
                    if ($cvService->hasReachedMaxCvs($user)) {
                        $fail('You have reached the maximum limit of '.CvService::MAX_CVS_PER_USER.' resumes. Please delete an older resume before uploading a new one.');
                    }
                },
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cv_file.max' => 'The resume file size must not exceed 5MB.',
        ];
    }
}
