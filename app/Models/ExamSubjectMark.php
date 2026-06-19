<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubjectMark extends Model
{
    protected $fillable = ['exam_id', 'program_id', 'year', 'subject_id', 'total_marks', 'sort_order'];

    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
}
