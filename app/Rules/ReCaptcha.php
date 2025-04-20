<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Log::info('reCAPTCHA v2 validation started:', [
            'attribute' => $attribute,
            'token' => $value,
            'site_key' => config('services.recaptcha.site_key'),
            'secret_key' => config('services.recaptcha.secret_key'),
        ]);

        if (empty($value)) {
            Log::warning('reCAPTCHA v2 token is empty or null.', [
                'attribute' => $attribute,
                'value' => $value,
                'site_key' => config('services.recaptcha.site_key'),
            ]);
            $fail('Please complete the reCAPTCHA checkbox.');
            return;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
            ]);

            if ($response->failed()) {
                Log::error('reCAPTCHA v2 API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'site_key' => config('services.recaptcha.site_key'),
                ]);
                $fail('Error verifying reCAPTCHA. Please try again.');
                return;
            }

            $data = $response->json();

            Log::info('reCAPTCHA v2 verification response:', [
                'response' => $data,
                'site_key' => config('services.recaptcha.site_key'),
            ]);

            if (!isset($data['success']) || !$data['success']) {
                Log::warning('reCAPTCHA v2 verification failed', [
                    'success' => $data['success'] ?? false,
                    'errors' => $data['error-codes'] ?? [],
                    'site_key' => config('services.recaptcha.site_key'),
                ]);
                $fail('reCAPTCHA verification failed. Please try again.');
            } else {
                Log::info('reCAPTCHA v2 verification passed:', [
                    'hostname' => $data['hostname'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('reCAPTCHA v2 verification exception:', [
                'message' => $e->getMessage(),
                'site_key' => config('services.recaptcha.site_key'),
            ]);
            $fail('Error verifying reCAPTCHA. Please try again.');
        }
    }
}