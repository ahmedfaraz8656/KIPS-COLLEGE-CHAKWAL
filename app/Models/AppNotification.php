<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'title', 'message', 'type', 'target_type', 'target_value',
        'channel', 'scheduled_at', 'sent_at', 'created_by',
    ];

    protected $casts = ['scheduled_at' => 'datetime', 'sent_at' => 'datetime'];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function recipients(): HasMany { return $this->hasMany(NotificationRecipient::class, 'notification_id'); }

    /** Resolve the actual list of User models this notification should reach. */
    public function resolveRecipients()
    {
        switch ($this->target_type) {
            case 'role':
                return User::role($this->target_value)->get();
            case 'campus':
                return User::where('campus', $this->target_value)->orWhere('campus', 'both')->get();
            case 'student':
                $student = Student::find($this->target_value);
                return $student?->section ? collect() : collect(); // Student/Parent portal accounts resolved separately
            case 'section':
                $studentUserIds = Student::where('section_id', $this->target_value)->pluck('id');
                return collect(); // placeholder until Student-User linking for portals is finalized
            default: // all
                return User::active()->get();
        }
    }
}
