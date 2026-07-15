<?php

namespace Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'is_paid',
        'is_unlimited',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'is_unlimited' => 'boolean',
        ];
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PlanLimit::class);
    }
}
