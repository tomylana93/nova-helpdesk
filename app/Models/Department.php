<?php

namespace App\Models;

use App\Enums\GeneralStatus;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property GeneralStatus $status
 * @property Branch|null $branch
 */
#[Fillable(['branch_id', 'code', 'name', 'status'])]
class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the branch that owns the department.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the users associated with the department.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GeneralStatus::class,
        ];
    }
}
