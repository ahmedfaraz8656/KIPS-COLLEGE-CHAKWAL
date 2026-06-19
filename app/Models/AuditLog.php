<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id', 'action', 'module', 'description',
        'before_values', 'after_values', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Quick static logger used throughout all controllers. */
    public static function record(string $action, string $module, string $description, array $before = [], array $after = []): void
    {
        static::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'module'        => $module,
            'description'   => $description,
            'before_values' => $before ?: null,
            'after_values'  => $after ?: null,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ]);
    }

    // Audit logs are immutable — no update/delete allowed, even for MD.
    public function delete()
    {
        throw new \RuntimeException('Audit logs cannot be deleted.');
    }
}
