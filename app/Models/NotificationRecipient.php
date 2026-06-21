<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRecipient extends Model
{
    protected $table = 'notification_recipients';

    protected $fillable = ['notification_id', 'user_id', 'read_at', 'whatsapp_status'];
    protected $casts = ['read_at' => 'datetime'];

    public function notification(): BelongsTo { return $this->belongsTo(AppNotification::class, 'notification_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function markRead(): void
    {
        if (!$this->read_at) $this->update(['read_at' => now()]);
    }
}
