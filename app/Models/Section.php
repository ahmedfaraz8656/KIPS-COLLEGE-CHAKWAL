<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Section extends Model
{
    protected $fillable = [
        'code', 'program_id', 'campus', 'year', 'capacity', 'is_combined', 'status',
    ];

    protected $casts = [
        'is_combined' => 'boolean',
        'status'      => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function incharge()
    {
        return $this->hasOne(SectionIncharge::class);
    }

    /**
     * STRICT RULE: Girls can never move to Boys sections and vice versa.
     * STRICT RULE: First Year students can never move to Second Year sections.
     * This scope returns ONLY valid move/transfer targets for a given student.
     */
    public function scopeValidMoveTargetsFor(Builder $query, Student $student)
    {
        return $query->where('campus', $student->campus)
                      ->where('year', $student->year)
                      ->where('id', '!=', $student->section_id)
                      ->where('status', true);
    }

    public function activeStudentCount(): int
    {
        return $this->students()->where('status', 'active')->count();
    }
}
