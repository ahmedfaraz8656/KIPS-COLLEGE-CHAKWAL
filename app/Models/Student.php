<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'roll_number', 'roll_number_manually_edited',
        'name', 'father_name', 'cnic_bform', 'dob', 'whatsapp', 'alternate_phone',
        'address', 'previous_school', 'photo',
        'campus', 'year', 'program_id', 'section_id', 'enrollment_date',
        'ninth_board', 'ninth_roll_no', 'ninth_year', 'ninth_total_marks', 'ninth_obtained_marks', 'ninth_stream',
        'tenth_board', 'tenth_roll_no', 'tenth_year', 'tenth_total_marks', 'tenth_obtained_marks', 'tenth_stream',
        'status', 'status_note', 'is_demo', 'created_by',
    ];

    protected $casts = [
        'dob' => 'date',
        'enrollment_date' => 'date',
        'is_demo' => 'boolean',
        'roll_number_manually_edited' => 'boolean',
    ];

    // ─── RELATIONSHIPS ───────────────────────────────────────────
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function sectionHistory(): HasMany { return $this->hasMany(StudentSectionHistory::class); }
    public function attendance(): HasMany { return $this->hasMany(Attendance::class); }
    public function marks(): HasMany { return $this->hasMany(StudentMark::class); }

    // ─── ACCESSORS ───────────────────────────────────────────────
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo ? asset('storage/'.$this->photo) : asset('images/default-student.png');
    }

    public function getNinthPercentAttribute(): ?float
    {
        if (!$this->ninth_total_marks) return null;
        return round(($this->ninth_obtained_marks / $this->ninth_total_marks) * 100, 1);
    }

    public function getTenthPercentAttribute(): ?float
    {
        if (!$this->tenth_total_marks) return null;
        return round(($this->tenth_obtained_marks / $this->tenth_total_marks) * 100, 1);
    }

    // ─── ROLL NUMBER GENERATION ──────────────────────────────────
    /**
     * Format: [Campus][Year][Program][4-digit sequence]
     * Example: B1C0001 = Boys, Year 1, Computer(ICS), Student 1
     *          G1M0015 = Girls, Year 1, Medical, Student 15
     * GUARANTEED UNIQUE across the entire system — checked before assignment.
     */
    public static function generateRollNumber(string $campus, string $year, Program $program): string
    {
        $campusCode  = $campus === 'boys' ? 'B' : 'G';
        $yearCode    = $year === 'first' ? '1' : '2';
        $programCode = match ($program->code) {
            'ICS'  => 'C',
            'MED'  => 'M',
            'ENG'  => 'E',
            'FAIT' => 'F',
            default => 'X',
        };

        $prefix = $campusCode.$yearCode.$programCode;

        do {
            $lastSequence = static::where('roll_number', 'like', $prefix.'%')
                ->orderByDesc('roll_number')
                ->value('roll_number');

            $nextNumber = $lastSequence
                ? ((int) substr($lastSequence, strlen($prefix))) + 1
                : 1;

            $candidate = $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        } while (static::where('roll_number', $candidate)->exists()); // duplicate-proof loop

        return $candidate;
    }

    // ─── MOVE / TRANSFER VALIDATION ──────────────────────────────
    /**
     * STRICT RULES enforced here (also enforced at controller + DB level):
     *  - Girls can only move to Girls sections; Boys only to Boys sections.
     *  - First Year students can only move within First Year sections.
     *  - Second Year students can only move within Second Year sections.
     */
    public function canMoveTo(Section $targetSection): bool
    {
        return $targetSection->campus === $this->campus
            && $targetSection->year === $this->year
            && $targetSection->id !== $this->section_id;
    }

    public function moveTo(Section $targetSection, ?int $performedBy = null, ?string $reason = null): bool
    {
        if (!$this->canMoveTo($targetSection)) {
            return false;
        }

        $fromSectionId = $this->section_id;

        $this->sectionHistory()->create([
            'from_section_id' => $fromSectionId,
            'to_section_id'   => $targetSection->id,
            'action'          => 'move',
            'reason'          => $reason,
            'performed_by'    => $performedBy,
        ]);

        $this->update([
            'section_id'  => $targetSection->id,
            'program_id'  => $targetSection->program_id,
            'status_note' => "Transferred from section #{$fromSectionId} to {$targetSection->code} on ".now()->format('d-M-Y'),
        ]);

        return true;
    }
}
