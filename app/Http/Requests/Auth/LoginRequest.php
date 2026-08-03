<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
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
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        // 1. Validate Math Captcha if parameters sent
        if ($this->has('captcha_num1') && $this->has('captcha_num2') && $this->has('captcha_operator')) {
            $n1 = (int) $this->input('captcha_num1');
            $n2 = (int) $this->input('captcha_num2');
            $op = $this->input('captcha_operator');
            $userAns = (int) $this->input('captcha_input');

            $expected = ($op === '-') ? ($n1 - $n2) : ($n1 + $n2);

            if ($userAns !== $expected) {
                throw ValidationException::withMessages([
                    'captcha_input' => 'Jawaban verifikasi captcha salah. Silakan coba lagi.',
                ]);
            }
        }

        // 2. Attempt user authentication
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }
    }
}
