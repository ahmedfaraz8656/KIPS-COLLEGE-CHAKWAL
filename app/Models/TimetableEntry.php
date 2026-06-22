<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEntry extends Model
{
    protected $fillable = ['section_id', 'day', 'period_slot_id', 'subject_id', 'teacher_id'];

    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function periodSlot(): BelongsTo { return $this->belongsTo(PeriodSlot::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }

    /**
     * Real-time conflict check: is this teacher already teaching another
     * SECTION at the same day+period? (Used before saving a new entry.)
     */
    public static function findConflict(int $teacherId, string $day, int $periodSlotId, ?int $excludeSectionId = null): ?self
    {
        return static::where('teacher_id', $teacherId)
            ->where('day', $day)
            ->where('period_slot_id', $periodSlotId)
            ->when($excludeSectionId, fn ($q) => $q->where('section_id', '!=', $excludeSectionId))
            ->with('section')
            ->first();
    }
}
