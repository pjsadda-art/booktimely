<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorAuditLog extends Model
{
    protected $table = 'two_factor_audit_logs';

    protected $fillable = [
        'user_id',
        'admin_user_id',
        'business_id',
        'action',
        'ip_address',
        'user_agent',
    ];

    public const SETUP_STARTED = '2fa_setup_started';
    public const ENABLED = '2fa_enabled';
    public const DISABLED = '2fa_disabled';
    public const VERIFY_SUCCESS = '2fa_verify_success';
    public const VERIFY_FAILED = '2fa_verify_failed';
    public const RECOVERY_CODE_USED = '2fa_recovery_code_used';
    public const RECOVERY_CODES_REGENERATED = '2fa_recovery_codes_regenerated';
    public const ADMIN_REQUIRED = '2fa_admin_required';
    public const ADMIN_DISABLED = '2fa_admin_disabled';
    public const ADMIN_RESET = '2fa_admin_reset';
    public const COMPANY_REQUIRE_ENABLED = '2fa_company_require_enabled';
    public const COMPANY_REQUIRE_DISABLED = '2fa_company_require_disabled';

    public static function record(string $action, int $userId, ?int $adminUserId = null, ?int $businessId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'admin_user_id' => $adminUserId,
            'business_id' => $businessId,
            'action' => $action,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
