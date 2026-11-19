<?php

namespace App\DTOs;

use App\Http\Requests\Cvs\StoreCvRequest;
use App\Http\Requests\Onboarding\StoreOnboardingCvRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class CvUploadData
{
    public function __construct(
        public UploadedFile $file,
        public ?string $name = null,
        public bool $isDefault = false,
    ) {}

    public static function fromRequest(StoreCvRequest|StoreOnboardingCvRequest|Request $request): self
    {
        /** @var UploadedFile $file */
        $file = $request->file('cv_file');

        return new self(
            file: $file,
            name: $request->input('name'),
            isDefault: $request->boolean('is_default'),
        );
    }
}
