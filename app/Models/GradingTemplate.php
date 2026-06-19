<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingTemplate extends Model
{
    protected $fillable = ['name', 'min_pass_percent', 'is_default'];
    protected $casts = ['is_default' => 'boolean'];

    public function rules(): HasMany
    {
        return $this->hasMany(GradingRule::class)->orderByDesc('from_percent');
    }

    /** Looks up the Grade + Remarks for a given percentage. */
    public function gradeFor(float $percent): ?GradingRule
    {
        return $this->rules()
            ->where('from_percent', '<=', $percent)
            ->where('to_percent', '>=', $percent)
            ->first();
    }
}

