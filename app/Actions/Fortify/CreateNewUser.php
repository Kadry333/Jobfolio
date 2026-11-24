<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'name' => ['required_without_all:firstName,lastName', 'nullable', 'string', 'max:255'],
            'firstName' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'lastName' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        $parts = explode(' ', trim($input['name'] ?? ''), 2);

        $user = User::create([
            'firstName' => $input['firstName'] ?? $parts[0],
            'lastName' => $input['lastName'] ?? ($parts[1] ?? ''),
            'email' => $input['email'],
            'password' => $input['password'],
            'email_verified_at' => now(),
        ]);

        try {
            Mail::to($user->email)->queue(new WelcomeMail($user));
        } catch (\Throwable $e) {
            Log::error('Failed to queue welcome email to '.$user->email.': '.$e->getMessage());
        }

        return $user;
    }
}
