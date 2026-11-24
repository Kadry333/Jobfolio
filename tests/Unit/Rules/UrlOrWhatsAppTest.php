<?php

namespace Tests\Unit\Rules;

use App\Rules\UrlOrWhatsApp;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UrlOrWhatsAppTest extends TestCase
{
    public function test_valid_urls_pass_validation(): void
    {
        $validUrls = [
            'https://jobs.lever.co/company/123',
            'http://company.com',
            'https://www.wuzzuf.net/jobs/12345',
            'linkedin.com/jobs/view/123456789',
            'wa.me/201012345678',
            'https://wa.me/201012345678',
            'api.whatsapp.com/send?phone=201012345678',
            'whatsapp://send?phone=201012345678',
        ];

        foreach ($validUrls as $url) {
            $validator = Validator::make(
                ['jobURL' => $url],
                ['jobURL' => ['nullable', new UrlOrWhatsApp]]
            );

            $this->assertTrue($validator->passes(), "Expected '{$url}' to pass validation.");
        }
    }

    public function test_valid_whatsapp_phone_numbers_pass_validation(): void
    {
        $validPhones = [
            '+201012345678',
            '01012345678',
            '00201012345678',
            '201012345678',
            '+1 (555) 123-4567',
            '+44 7911 123456',
            '012-345-6789',
        ];

        foreach ($validPhones as $phone) {
            $validator = Validator::make(
                ['jobURL' => $phone],
                ['jobURL' => ['nullable', new UrlOrWhatsApp]]
            );

            $this->assertTrue($validator->passes(), "Expected phone '{$phone}' to pass validation.");
        }
    }

    public function test_null_or_empty_passes_when_nullable(): void
    {
        $validator = Validator::make(
            ['jobURL' => null],
            ['jobURL' => ['nullable', new UrlOrWhatsApp]]
        );
        $this->assertTrue($validator->passes());

        $validator = Validator::make(
            ['jobURL' => ''],
            ['jobURL' => ['nullable', new UrlOrWhatsApp]]
        );
        $this->assertTrue($validator->passes());
    }

    public function test_invalid_strings_fail_validation(): void
    {
        $invalidStrings = [
            'just a job title',
            '123',
            'invalid@not-a-phone-or-domain',
            'not a valid url or number',
        ];

        foreach ($invalidStrings as $string) {
            $validator = Validator::make(
                ['jobURL' => $string],
                ['jobURL' => ['nullable', new UrlOrWhatsApp]]
            );

            $this->assertFalse($validator->passes(), "Expected '{$string}' to fail validation.");
        }
    }
}
