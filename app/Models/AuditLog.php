<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'trx_audit_logs';
    protected $fillable = [
        'user_id',
        'username',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Parse raw user agent string into a friendly human-readable format.
     */
    public static function parseUserAgent(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return '-';
        }

        // Determine OS
        $os = 'Unknown OS';
        if (stripos($userAgent, 'windows nt 10.0') !== false) {
            $os = 'Windows 10/11';
        } elseif (stripos($userAgent, 'windows nt 6.3') !== false) {
            $os = 'Windows 8.1';
        } elseif (stripos($userAgent, 'windows nt 6.2') !== false) {
            $os = 'Windows 8';
        } elseif (stripos($userAgent, 'windows nt 6.1') !== false) {
            $os = 'Windows 7';
        } elseif (stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false) {
            $os = 'iOS';
        } elseif (stripos($userAgent, 'macintosh') !== false || stripos($userAgent, 'mac os x') !== false) {
            $os = 'macOS';
        } elseif (stripos($userAgent, 'android') !== false) {
            $os = 'Android';
        } elseif (stripos($userAgent, 'linux') !== false) {
            $os = 'Linux';
        }

        // Determine Browser
        $browser = 'Unknown Browser';
        if (stripos($userAgent, 'brave') !== false) {
            $browser = 'Brave';
        } elseif (stripos($userAgent, 'edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'opr') !== false || stripos($userAgent, 'opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($userAgent, 'chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'safari') !== false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'msie') !== false || stripos($userAgent, 'trident') !== false) {
            $browser = 'Internet Explorer';
        }

        return "$browser ($os)";
    }
}
