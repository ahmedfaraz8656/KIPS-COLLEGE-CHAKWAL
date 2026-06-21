<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    protected $fillable = ['fee_category_id', 'program_id', 'campus', 'year', 'amount', 'installment_plan'];

    public function category(): BelongsTo { return $this->belongsTo(FeeCategory::class, 'fee_category_id'); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
}
