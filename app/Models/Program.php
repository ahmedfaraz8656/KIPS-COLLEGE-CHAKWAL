<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Program extends Model
{
    protected $fillable = ['code', 'name', 'campus_scope', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'program_subject')
            ->withPivot(['year', 'default_marks', 'sort_order', 'is_rotating', 'rotation_group']);
    }

    public function subjectsForYear(string $year)
    {
        return $this->subjects()->wherePivot('year', $year)->orderBy('program_subject.sort_order');
    }
}
