<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMark extends Model
{
    protected $fillable = [
        'student_id', 'exam_id', 'subject_id', 'section_id',
        'total_marks', 'obtained_marks', 'is_absent', 'is_leave',
        'entered_by', 'entered_at',
    ];

    protected $casts = [
        'is_absent' => 'boolean',
        'is_leave' => 'boolean',
        'entered_at' => 'datetime',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function enteredBy(): BelongsTo { return $this->belongsTo(User::class, 'entered_by'); }

    /**
     * CRITICAL BUSINESS RULE (per Ahmed's spec):
     * If a student was marked Absent OR on Leave in the Attendance module
     * on the exam date, their obtained_marks MUST be forced to 0 for every
     * subject in that exam — they cannot be given any marks, and any
     * cumulative/result calculation must treat this as zero.
     */
    protected static function booted(): void
    {
        static::saving(function (StudentMark $mark) {
            if ($mark->is_absent || $mark->is_leave) {
                $mark->obtained_marks = 0;
            }
        });
    }

    public function getPercentAttribute(): float
    {
        return $this->total_marks > 0
            ? round(($this->obtained_marks / $this->total_marks) * 100, 2)
            : 0;
    }
}
