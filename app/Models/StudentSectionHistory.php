<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSectionHistory extends Model
{
    protected $fillable = ['student_id', 'from_section_id', 'to_section_id', 'action', 'reason', 'performed_by'];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function fromSection(): BelongsTo { return $this->belongsTo(Section::class, 'from_section_id'); }
    public function toSection(): BelongsTo { return $this->belongsTo(Section::class, 'to_section_id'); }
    public function performedBy(): BelongsTo { return $this->belongsTo(User::class, 'performed_by'); }
}
