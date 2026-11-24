<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class PasswordStrengthTest extends TestCase
{
    public function test_strong_password_rules_require_length_letters_numbers_and_symbols(): void
    {
        $rule = Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();

        // Too short (< 10)
        $validator = Validator::make(['password' => 'Aa1!'], ['password' => [$rule]]);
        $this->assertTrue($validator->fails());

        // Missing number
        $validator = Validator::make(['password' => 'Password!#'], ['password' => [$rule]]);
        $this->assertTrue($validator->fails());

        // Missing uppercase
        $validator = Validator::make(['password' => 'password123!'], ['password' => [$rule]]);
        $this->assertTrue($validator->fails());

        // Missing symbol
        $validator = Validator::make(['password' => 'Password1234'], ['password' => [$rule]]);
        $this->assertTrue($validator->fails());

        // Valid strong password (meets all requirements)
        $validator = Validator::make(['password' => 'Password123!#'], ['password' => [$rule]]);
        $this->assertFalse($validator->fails());
    }
}
