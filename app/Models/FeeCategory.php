<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    protected $fillable = ['name', 'is_recurring'];
    protected $casts = ['is_recurring' => 'boolean'];

    public function structures(): HasMany { return $this->hasMany(FeeStructure::class); }
}
