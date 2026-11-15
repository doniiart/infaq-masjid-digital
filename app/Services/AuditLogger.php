<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id'        => $userId ?? Auth::id(),
            'action'         => $action,
            'auditable_type' => $auditable ? $auditable->getMorphClass() : null,
            'auditable_id'   => $auditable?->getKey(),
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => $request?->ip(),
            'user_agent'     => $request?->userAgent(),
            'created_at'     => now(),
        ]);
    }
}
