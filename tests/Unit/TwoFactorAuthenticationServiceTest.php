<?php

namespace Tests\Unit;

use App\Services\TwoFactorAuthenticationService;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationServiceTest extends TestCase
{
    protected TwoFactorAuthenticationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TwoFactorAuthenticationService();
    }

    public function test_generates_a_valid_base32_secret(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertNotEmpty($secret);
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_verifies_a_correct_totp_code(): void
    {
        $secret = $this->service->generateSecret();
        $code = (new Google2FA())->getCurrentOtp($secret);

        $this->assertTrue($this->service->verify($secret, $code));
    }

    public function test_rejects_an_incorrect_totp_code(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertFalse($this->service->verify($secret, '000000'));
    }

    public function test_rejects_non_numeric_or_wrong_length_codes(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertFalse($this->service->verify($secret, 'abcdef'));
        $this->assertFalse($this->service->verify($secret, '12345'));
        $this->assertFalse($this->service->verify($secret, '1234567'));
    }

    public function test_otpauth_uri_contains_issuer_and_email(): void
    {
        $user = new \App\Models\User(['email' => 'test@example.com']);
        $secret = $this->service->generateSecret();

        $uri = $this->service->otpauthUri($user, $secret);

        $this->assertStringStartsWith('otpauth://totp/', $uri);
        $this->assertStringContainsString(rawurlencode($user->email), $uri);
    }
}
