<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingRule extends Model
{
    protected $fillable = ['grading_template_id', 'from_percent', 'to_percent', 'grade', 'remarks'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(GradingTemplate::class, 'grading_template_id');
    }
}
