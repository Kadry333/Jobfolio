<?php

namespace App\DTOs;

use App\Enums\TrackedJobStatus;
use App\Http\Requests\TrackedJobs\UpdateTrackedJobRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

readonly class UpdateTrackedJobData
{
    /**
     * @param  list<JobContactData>|null  $contacts
     */
    public function __construct(
        public ?string $jobTitle = null,
        public ?string $companyName = null,
        public ?TrackedJobStatus $status = null,
        public ?string $jobURL = null,
        public ?int $cvId = null,
        public ?string $notes = null,
        public ?Carbon $appliedAt = null,
        public ?array $contacts = null,
    ) {}

    public static function fromRequest(UpdateTrackedJobRequest|Request $request): self
    {
        $status = null;
        if ($request->filled('status')) {
            $statusValue = $request->input('status');
            $status = $statusValue instanceof TrackedJobStatus
                ? $statusValue
                : TrackedJobStatus::from($statusValue);
        }

        $appliedAt = $request->input('appliedAt');

        $contacts = null;
        if ($request->has('contacts')) {
            $rawContacts = $request->input('contacts', []);
            $contacts = [];
            if (is_array($rawContacts)) {
                foreach ($rawContacts as $rawContact) {
                    if (is_array($rawContact) && ! empty($rawContact['name'])) {
                        $contacts[] = JobContactData::fromArray($rawContact);
                    }
                }
            }
        }

        return new self(
            jobTitle: $request->input('jobTitle'),
            companyName: $request->input('companyName'),
            status: $status,
            jobURL: $request->input('jobURL'),
            cvId: $request->has('cv_id') ? ($request->filled('cv_id') ? (int) $request->input('cv_id') : null) : null,
            notes: $request->input('notes'),
            appliedAt: $appliedAt ? Carbon::parse($appliedAt) : null,
            contacts: $contacts,
        );
    }

    /**
     * Convert non-null properties to an associative array for Eloquent update.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->jobTitle !== null) {
            $data['jobTitle'] = $this->jobTitle;
        }
        if ($this->companyName !== null) {
            $data['companyName'] = $this->companyName;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }
        if ($this->jobURL !== null) {
            $data['jobURL'] = $this->jobURL;
        }
        if ($this->cvId !== null) {
            $data['cv_id'] = $this->cvId;
        }
        if ($this->notes !== null) {
            $data['notes'] = $this->notes;
        }
        if ($this->appliedAt !== null) {
            $data['appliedAt'] = $this->appliedAt;
        }

        return $data;
    }
}
