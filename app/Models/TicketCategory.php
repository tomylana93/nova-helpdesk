<?php

namespace App\Models;

use App\Enums\GeneralStatus;
use Database\Factories\TicketCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property GeneralStatus $status
 * @property TicketCategory|null $parent
 * @property Collection<int, TicketCategory> $subcategories
 */
#[Fillable(['parent_id', 'name', 'description', 'status'])]
class TicketCategory extends Model
{
    /** @use HasFactory<TicketCategoryFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the parent category.
     *
     * @return BelongsTo<TicketCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the subcategories.
     *
     * @return HasMany<TicketCategory, $this>
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
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
