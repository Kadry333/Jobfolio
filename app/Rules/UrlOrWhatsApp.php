<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UrlOrWhatsApp implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid website URL or WhatsApp number.');

            return;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return;
        }

        // 1. WhatsApp link or URL with scheme
        if (preg_match('/^(https?:\/\/|whatsapp:\/\/)/i', $trimmed)) {
            if (filter_var($trimmed, FILTER_VALIDATE_URL) || str_starts_with($trimmed, 'whatsapp://')) {
                return;
            }
        }

        // 2. wa.me or api.whatsapp.com link without scheme
        if (preg_match('/^(wa\.me|api\.whatsapp\.com)\//i', $trimmed)) {
            return;
        }

        // 3. Domain-like URL without scheme (e.g. linkedin.com/jobs/123 or www.example.com)
        if (preg_match('/^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/', $trimmed)) {
            return;
        }

        // 4. Phone number / WhatsApp number:
        // Allows optional leading +, digits, spaces, hyphens, periods, parentheses.
        // Must contain at least 6 digits and only valid phone characters.
        $digitsOnly = preg_replace('/[^0-9]/', '', $trimmed);
        if (strlen($digitsOnly) >= 6 && strlen($digitsOnly) <= 20) {
            if (preg_match('/^[+0-9\s().-]+$/', $trimmed)) {
                return;
            }
        }

        $fail('The :attribute must be a valid website URL or WhatsApp number.');
    }
}
