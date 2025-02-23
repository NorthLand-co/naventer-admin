<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

class ChangePasswordRequest extends FormRequest
{
    private $id = null;

    protected function prepareForValidation()
    {
        $this->id = User::where('email', $this->input('username'))
            ->orWhere('phone', ltrim($this->input('username'), '0'))
            ->first()->id;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'otp' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to update users password.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changePassword(): void
    {
        // Rate limiting: Allow up to 3 requests per minute per IP
        $key = 'change-pass:'.$this->getClientRealIp();
        $maxAttempts = 5; // Max allowed attempts
        $decaySeconds = 60; // Time period (in seconds)

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'auth' => __('auth.rate_limit'),
            ]);
        }

        // Increment rate limiter count
        RateLimiter::hit($key, $decaySeconds);

        // Verify OTP
        if ($this->input('otp') !== $this->getOtp()) {
            throw ValidationException::withMessages([
                'otp' => __('auth.invalid_otp'),
            ]);
        }

        // Update password
        $user = User::find($this->id);
        $user->password = Hash::make($this->input('password'));
        $user->save();

        // Clear rate limiter and OTP
        RateLimiter::clear($key);
        $this->clearOtp();
    }

    /**
     * Return One time password for user login
     */
    private function getOtp()
    {
        $key = 'otp:'.$this->id;

        return Redis::get($key);
    }

    /**
     * Clear One time password from cache
     */
    public function clearOtp()
    {
        $key = 'otp:'.$this->id;
        Redis::del($key);
    }
}
