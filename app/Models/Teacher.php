<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'father_name', 'cnic', 'whatsapp', 'alternate_phone',
        'email', 'date_of_joining', 'gender', 'qualification', 'photo',
        'campus_access', 'status', 'is_demo',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'status' => 'boolean',
        'is_demo' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function sectionAssignments(): HasMany { return $this->hasMany(TeacherSection::class); }
    public function inchargeOf(): HasMany { return $this->hasMany(SectionIncharge::class); }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo ? asset('storage/'.$this->photo) : asset('images/default-avatar.png');
    }

    /** Number of distinct sections this teacher is assigned to. */
    public function getSectionsCountAttribute(): int
    {
        return $this->sectionAssignments()->distinct('section_id')->count('section_id');
    }

    /** Number of distinct subjects this teacher teaches across all sections. */
    public function getSubjectsCountAttribute(): int
    {
        return $this->sectionAssignments()->distinct('subject_id')->count('subject_id');
    }
}
