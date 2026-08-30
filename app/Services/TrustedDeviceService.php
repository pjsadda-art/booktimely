<?php

namespace App\Services;

use App\Models\TrustedDevice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * "Trust this device" for the two-factor challenge: skip the code prompt on
 * a browser that already proved it once, for a duration the user picked.
 *
 * The cookie only ever carries a random, unguessable token — never a user id
 * or anything else that would let it be forged. The token is looked up
 * against its SHA-256 hash in `trusted_devices`, the same reason password
 * reset tokens are hashed rather than stored raw.
 */
class TrustedDeviceService
{
    public const COOKIE_NAME = 'tfa_trusted_device';

    /**
     * @var array<string,int> duration key => minutes
     */
    public const DURATIONS = [
        '1_day' => 1 * 24 * 60,
        '1_week' => 7 * 24 * 60,
        '1_month' => 30 * 24 * 60,
        '1_qtr' => 90 * 24 * 60,
    ];

    /**
     * Whether the request's cookie names a live, unexpired trust record for
     * this user. Touches last_used_at so stale entries are visible if this
     * ever grows a "manage your devices" screen.
     */
    public function isTrusted(Request $request, User $user): bool
    {
        $token = $request->cookie(self::COOKIE_NAME);

        if (empty($token)) {
            return false;
        }

        $device = TrustedDevice::where('user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (!$device) {
            return false;
        }

        $device->last_used_at = now();
        $device->save();

        return true;
    }

    /**
     * Create a new trust record for $duration ('1_day'|'1_week'|'1_month'|'1_qtr')
     * and queue the cookie. Returns false (no-op) for an unknown/"never" key.
     */
    public function trust(Request $request, User $user, string $duration): bool
    {
        if (!isset(self::DURATIONS[$duration])) {
            return false;
        }

        $minutes = self::DURATIONS[$duration];
        $token = Str::random(64);

        TrustedDevice::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'ip_address' => $request->ip(),
            'expires_at' => Carbon::now()->addMinutes($minutes),
        ]);

        Cookie::queue(Cookie::make(
            self::COOKIE_NAME,
            $token,
            $minutes,
            null,
            null,
            true,   // secure
            true,   // httpOnly
            false,
            'Lax'
        ));

        return true;
    }
}
