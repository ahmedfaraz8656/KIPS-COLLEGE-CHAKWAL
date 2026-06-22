<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicEvent extends Model
{
    protected $fillable = ['date', 'title', 'description', 'campus_scope', 'created_by'];
    protected $casts = ['date' => 'date'];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
