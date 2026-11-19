<?php

namespace App\DTOs;

use App\Enums\TrackedJobStatus;
use App\Http\Requests\TrackedJobs\StoreTrackedJobRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class TrackedJobData
{
    /**
     * @param  list<JobContactData>  $contacts
     */
    public function __construct(
        public string $jobTitle,
        public string $companyName,
        public TrackedJobStatus $status,
        public ?string $jobURL = null,
        public ?string $cvSelection = 'default',
        public ?int $cvId = null,
        public ?UploadedFile $newCvFile = null,
        public ?string $newCvName = null,
        public bool $makeDefault = false,
        public ?string $notes = null,
        public ?Carbon $appliedAt = null,
        public array $contacts = [],
    ) {}

    public static function fromRequest(StoreTrackedJobRequest|Request $request): self
    {
        $statusValue = $request->input('status');
        $status = $statusValue instanceof TrackedJobStatus
            ? $statusValue
            : TrackedJobStatus::from($statusValue);

        $appliedAt = $request->input('appliedAt');

        $rawContacts = $request->input('contacts', []);
        $contacts = [];
        if (is_array($rawContacts)) {
            foreach ($rawContacts as $rawContact) {
                if (is_array($rawContact) && ! empty($rawContact['name'])) {
                    $contacts[] = JobContactData::fromArray($rawContact);
                }
            }
        }

        return new self(
            jobTitle: (string) $request->input('jobTitle'),
            companyName: (string) $request->input('companyName'),
            status: $status,
            jobURL: $request->input('jobURL'),
            cvSelection: $request->input('cv_selection', 'default'),
            cvId: $request->filled('cv_id') ? (int) $request->input('cv_id') : null,
            newCvFile: $request->file('new_cv_file'),
            newCvName: $request->input('new_cv_name'),
            makeDefault: $request->boolean('make_default'),
            notes: $request->input('notes'),
            appliedAt: $appliedAt ? Carbon::parse($appliedAt) : null,
            contacts: $contacts,
        );
    }
}
