<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
        'phone', 'whatsapp', 'gender',
        'photo', 'campus', 'status',
        'access_expires_at',
        'last_login_at', 'last_login_ip',
        'force_password_change',
        'is_demo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'     => 'datetime',
        'last_login_at'         => 'datetime',
        'access_expires_at'     => 'datetime',
        'password'              => 'hashed',
        'status'                => 'boolean',
        'force_password_change' => 'boolean',
        'is_demo'               => 'boolean',
    ];

    // ─── ACCESSORS ───────────────────────────────────────────────
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/default-avatar.png');
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials;
    }

    // ─── SCOPES ──────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->role($role);
    }

    // ─── HELPERS ─────────────────────────────────────────────────
    public function isActive(): bool
    {
        return (bool) $this->status;
    }

    public function primaryRole(): string
    {
        $roleOrder = [
            'Managing Director', 'Principal', 'Admin',
            'Exam Controller', 'Teacher', 'Class Incharge',
            'Student', 'Parent'
        ];

        foreach ($roleOrder as $role) {
            if ($this->hasRole($role)) return $role;
        }
        return 'User';
    }

    public function primaryRoleSlug(): string
    {
        return Str::slug($this->primaryRole());
    }

    public function dashboardRoute(): string
    {
        return route('dashboard');
    }

    /** Used by Class Incharge dashboard / Attendance permission checks. */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /** Used when a Student-role user logs in to find their own record. */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /** Used when a Parent-role user logs in to find their linked children. */
    public function children()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'user_id', 'student_id');
    }

    /** Used by EnsureUserIsActive middleware for temporary-access accounts. */
    public function hasExpiredAccess(): bool
    {
        return $this->access_expires_at && now()->greaterThan($this->access_expires_at);
    }
}
