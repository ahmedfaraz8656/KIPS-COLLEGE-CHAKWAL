<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $fillable = ['date', 'name', 'type', 'campus_scope', 'created_by'];
    protected $casts = ['date' => 'date'];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    /** Is this exact date+campus marked as a holiday (excluded from working days)? */
    public static function isHoliday(string $date, string $campus): bool
    {
        return static::where('date', $date)
            ->where(function ($q) use ($campus) {
                $q->where('campus_scope', 'both')->orWhere('campus_scope', $campus);
            })->exists();
    }
}
