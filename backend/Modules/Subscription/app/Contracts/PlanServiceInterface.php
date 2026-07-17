<?php

namespace Modules\Subscription\Contracts;

use App\Models\User;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\PlanLimit;
use Modules\Subscription\Models\UserSubscription;

interface PlanServiceInterface
{
    public function activeSubscription(User $user): ?UserSubscription;

    public function planFor(User $user): ?Plan;

    public function isUnlimited(User $user): bool;

    public function limitFor(User $user, string $resource): ?PlanLimit;

    public function currentUsage(User $user, string $resource): int;

    public function hasReachedLimit(User $user, string $resource): bool;

    public function canUseFeature(User $user, string $feature): bool;

    /**
     * @return array{status: string, started_at: \Illuminate\Support\Carbon, ends_at: ?\Illuminate\Support\Carbon, plan: Plan, limits: array<int, array{resource: string, limit: ?int, used: int, unlimited: bool}>}|null
     */
    public function summaryFor(User $user): ?array;
}
