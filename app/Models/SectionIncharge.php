<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionIncharge extends Model
{
    protected $fillable = ['section_id', 'teacher_id', 'substitute_teacher_id'];

    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function substituteTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'substitute_teacher_id'); }

    /**
     * Used by the UI to show: "PCB1 already has [Teacher] as incharge. Replace?"
     */
    public static function currentInchargeOf(Section $section): ?Teacher
    {
        return static::where('section_id', $section->id)->first()?->teacher;
    }
}
