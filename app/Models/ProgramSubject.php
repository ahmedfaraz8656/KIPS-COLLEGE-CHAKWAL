<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramSubject extends Model
{
    protected $table = 'program_subject';

    protected $fillable = [
        'program_id', 'subject_id', 'year', 'default_marks',
        'sort_order', 'is_rotating', 'rotation_group',
    ];

    protected $casts = ['is_rotating' => 'boolean'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
