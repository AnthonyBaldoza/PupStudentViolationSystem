<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ViolationReport::class);
    }

    /** Only categories that officers may currently choose from. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
