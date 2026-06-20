<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exam extends Model
{
    protected $fillable = [
        'name', 'type', 'sequence', 'exam_date', 'campus_scope', 'year_scope', 'description',
        'grading_template_id', 'marks_due_date', 'marks_due_date_extended_to',
        'is_locked', 'is_demo', 'created_by',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'marks_due_date' => 'datetime',
        'marks_due_date_extended_to' => 'datetime',
        'is_locked' => 'boolean',
        'is_demo' => 'boolean',
    ];

    public function gradingTemplate(): BelongsTo { return $this->belongsTo(GradingTemplate::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'exam_sections');
    }

    public function subjectMarks(): HasMany
    {
        return $this->hasMany(ExamSubjectMark::class);
    }

    public function studentMarks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }

    /**
     * Effective deadline for marks entry — uses the extended date if Principal
     * has pushed it back, otherwise the original due date.
     */
    public function getEffectiveDueDateAttribute()
    {
        return $this->marks_due_date_extended_to ?? $this->marks_due_date;
    }

    public function isPastDue(): bool
    {
        $due = $this->effective_due_date;
        return $due && now()->greaterThan($due);
    }

    /**
     * Teachers who have NOT finished entering marks for their assigned
     * subject/sections in this exam — shown to Admin/Principal after due date.
     */
    public function teachersWithIncompleteMarks()
    {
        return TeacherSection::query()
            ->whereIn('section_id', $this->sections()->pluck('sections.id'))
            ->get()
            ->filter(function ($assignment) {
                $expected = Student::where('section_id', $assignment->section_id)
                    ->where('status', 'active')->count();
                $entered = StudentMark::where('exam_id', $this->id)
                    ->where('subject_id', $assignment->subject_id)
                    ->whereHas('student', fn ($q) => $q->where('section_id', $assignment->section_id))
                    ->count();
                return $entered < $expected;
            });
    }
}
