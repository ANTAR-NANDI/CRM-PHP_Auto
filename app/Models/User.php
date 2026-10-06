<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'employee_code',
        'email',
        'phone',
        'designation',
        'joining_date',
        'salary',
        'address',
        'store_id',
        'store_position_id',
        'department_id',
        'chart_of_account_id',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'joining_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function storePosition(): BelongsTo
    {
        return $this->belongsTo(StorePosition::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Check roles while supporting employees created before Spatie roles were enabled.
     * A legacy users.role value is considered only when the account has no assigned role.
     *
     * @param  array<int, string>  $roles
     */
    public function hasAnyPharmacyRole(array $roles): bool
    {
        $assignedRoles = $this->getRoleNames();

        if ($assignedRoles->isNotEmpty()) {
            return $assignedRoles->intersect($roles)->isNotEmpty();
        }

        return in_array($this->role, $roles, true);
    }

    /** System administrators always retain access while module permissions evolve. */
    public function isAdministrator(): bool
    {
        return $this->hasRole('admin')
            || in_array(strtolower((string) $this->role), ['admin', 'administrator', 'system admin'], true)
            || strtolower((string) $this->designation) === 'system admin';
    }

}
