<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'student_id', 'section_id', 'date', 'status', 'is_late',
        'marked_at_time', 'marked_by', 'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'is_late' => 'boolean',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function markedBy(): BelongsTo { return $this->belongsTo(User::class, 'marked_by'); }

    /**
     * Determines if marking this attendance NOW (at $markedAtTime) counts as
     * Late, based on the dynamic college-wide setting (Settings::attendance_late_time).
     * Admin/Principal/MD can change this cutoff anytime — change is NOT
     * retroactive to already-saved records.
     */
    public static function isLateArrival(string $markedAtTime): bool
    {
        $cutoff = Setting::get('attendance_late_time', '08:30');
        return $markedAtTime > $cutoff;
    }
}
