<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Max allowed login/captcha attempts by IP before 24-hour block.
     */
    public const MAX_IP_ATTEMPTS = 3;
    public const DECAY_SECONDS = 86400; // 24 Hours

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'captcha_num1' => ['nullable', 'integer'],
            'captcha_num2' => ['nullable', 'integer'],
            'captcha_operator' => ['nullable', 'string'],
            'captcha_input' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'email.email' => 'Silakan masukkan alamat email yang valid.',
        ];
    }

    /**
     * Get the rate limiting throttle key for the client IP.
     */
    public function ipThrottleKey(): string
    {
        return 'login_ip_block:' . $this->ip();
    }

    /**
     * Check if the client IP is blocked due to 3 failed attempts in 24 hours.
     *
     * @throws ValidationException
     */
    public function ensureIpIsNotBlocked(): void
    {
        $key = $this->ipThrottleKey();

        if (! RateLimiter::tooManyAttempts($key, self::MAX_IP_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($key);
        $hours = floor($seconds / 3600);
        $minutes = ceil(($seconds % 3600) / 60);

        $timeText = $hours > 0
            ? "{$hours} jam {$minutes} menit"
            : "{$seconds} detik";

        throw ValidationException::withMessages([
            'email' => "Alamat IP Anda ({$this->ip()}) diblokir dari melakukan login selama 24 jam karena 3 kali gagal verifikasi/login. Silakan coba lagi dalam {$timeText}.",
        ]);
    }

    /**
     * Record a failed attempt for the IP.
     */
    public function hitIpThrottle(): void
    {
        RateLimiter::hit($this->ipThrottleKey(), self::DECAY_SECONDS);
    }

    /**
     * Clear failed attempts for the IP on successful login.
     */
    public function clearIpThrottle(): void
    {
        RateLimiter::clear($this->ipThrottleKey());
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        // 1. Ensure IP is not blocked for 24 hours
        $this->ensureIpIsNotBlocked();

        // 2. Validate Math Captcha if parameters sent
        if ($this->has('captcha_num1') && $this->has('captcha_num2') && $this->has('captcha_operator')) {
            $n1 = (int) $this->input('captcha_num1');
            $n2 = (int) $this->input('captcha_num2');
            $op = $this->input('captcha_operator');
            $userAns = (int) $this->input('captcha_input');

            $expected = ($op === '-') ? ($n1 - $n2) : ($n1 + $n2);

            if ($userAns !== $expected) {
                $this->hitIpThrottle();

                $key = $this->ipThrottleKey();
                $attempts = RateLimiter::attempts($key);
                $remaining = max(0, self::MAX_IP_ATTEMPTS - $attempts);

                if ($attempts >= self::MAX_IP_ATTEMPTS) {
                    $this->ensureIpIsNotBlocked();
                }

                throw ValidationException::withMessages([
                    'captcha_input' => "Jawaban verifikasi captcha salah. Sisa kesempatan login: {$remaining} kali.",
                ]);
            }
        }

        // 3. Attempt user authentication
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->hitIpThrottle();

            $key = $this->ipThrottleKey();
            $attempts = RateLimiter::attempts($key);
            $remaining = max(0, self::MAX_IP_ATTEMPTS - $attempts);

            if ($attempts >= self::MAX_IP_ATTEMPTS) {
                $this->ensureIpIsNotBlocked();
            }

            throw ValidationException::withMessages([
                'email' => "Email atau password yang Anda masukkan salah. Sisa kesempatan login: {$remaining} kali.",
            ]);
        }

        // 4. On success, reset IP block counter
        $this->clearIpThrottle();
    }
}
