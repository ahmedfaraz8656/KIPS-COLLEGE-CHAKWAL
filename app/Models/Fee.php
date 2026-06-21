<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Fee extends Model
{
    protected $fillable = [
        'student_id', 'fee_category_id', 'payment_date', 'amount_due', 'amount_paid',
        'waiver_amount', 'waiver_reason', 'payment_mode', 'receipt_number', 'remarks',
        'created_by', 'is_demo',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'is_demo' => 'boolean',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function category(): BelongsTo { return $this->belongsTo(FeeCategory::class, 'fee_category_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->amount_due - $this->amount_paid - $this->waiver_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->balance > 0 && $this->payment_date->isPast();
    }

    public static function generateReceiptNumber(): string
    {
        do {
            $candidate = 'RCPT-'.now()->format('Ym').'-'.str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('receipt_number', $candidate)->exists());

        return $candidate;
    }
}
