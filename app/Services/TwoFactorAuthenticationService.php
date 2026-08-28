<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTwoFactorRecoveryCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorAuthenticationService
{
    protected Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function otpauthUri(User $user, string $secret): string
    {
        $issuer = config('app.name', 'App');

        return $this->engine->getQRCodeUrl($issuer, $user->email, $secret);
    }

    public function qrCodeSvg(string $otpauthUri): string
    {
        return QrCode::size(200)->generate($otpauthUri);
    }

    public function verify(string $secret, string $code): bool
    {
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        return $this->engine->verifyKey($secret, $code, 1);
    }

    /**
     * @return array<int, string> plaintext codes (only ever returned here, never persisted raw)
     */
    public function generateRecoveryCodes(User $user, int $count = 8): array
    {
        UserTwoFactorRecoveryCode::where('user_id', $user->id)->delete();

        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = Str::upper(Str::random(4) . '-' . Str::random(4));
            $codes[] = $code;

            UserTwoFactorRecoveryCode::create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
            ]);
        }

        return $codes;
    }

    public function verifyAndConsumeRecoveryCode(User $user, string $code): bool
    {
        $unused = UserTwoFactorRecoveryCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->get();

        foreach ($unused as $recoveryCode) {
            if (Hash::check($code, $recoveryCode->code_hash)) {
                $recoveryCode->update(['used_at' => now()]);
                return true;
            }
        }

        return false;
    }
}
