<?php

namespace App\DTOs;

use App\Enums\JobContactStatus;

readonly class JobContactData
{
    public function __construct(
        public string $name,
        public JobContactStatus $status = JobContactStatus::WillContact,
        public ?string $email = null,
        public ?string $position = null,
        public ?string $notes = null,
        public ?string $linkedinUrl = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $statusValue = $data['status'] ?? JobContactStatus::WillContact->value;
        $status = $statusValue instanceof JobContactStatus
            ? $statusValue
            : (JobContactStatus::tryFrom((string) $statusValue) ?? JobContactStatus::WillContact);

        return new self(
            name: trim((string) ($data['name'] ?? '')),
            status: $status,
            email: ! empty($data['email']) ? trim((string) $data['email']) : null,
            position: ! empty($data['position']) ? trim((string) $data['position']) : null,
            notes: ! empty($data['notes']) ? trim((string) $data['notes']) : null,
            linkedinUrl: ! empty($data['linkedin_url']) ? trim((string) $data['linkedin_url']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status,
            'email' => $this->email,
            'position' => $this->position,
            'notes' => $this->notes,
            'linkedin_url' => $this->linkedinUrl,
        ];
    }
}
