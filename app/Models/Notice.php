<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Notice extends Model
{
    protected $fillable = [
        'title', 'content', 'target', 'campus_scope', 'priority',
        'attachment', 'post_date', 'expiry_date', 'is_archived', 'created_by',
    ];

    protected $casts = [
        'post_date' => 'datetime',
        'expiry_date' => 'date',
        'is_archived' => 'boolean',
    ];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeVisible(Builder $query)
    {
        return $query->where('is_archived', false)
            ->where(fn ($q) => $q->whereNull('post_date')->orWhere('post_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()));
    }

    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    public function reads()
    {
        return $this->hasMany(\App\Models\NoticeRead::class);
    }
}
