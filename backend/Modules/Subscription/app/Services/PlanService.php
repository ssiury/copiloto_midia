<?php

namespace Modules\Subscription\Services;

use App\Models\User;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\PlanLimit;
use Modules\Subscription\Models\UserSubscription;

class PlanService
{
    public function activeSubscription(User $user): ?UserSubscription
    {
        return UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan.limits')
            ->latest('started_at')
            ->first();
    }

    public function planFor(User $user): ?Plan
    {
        return $this->activeSubscription($user)?->plan;
    }

    public function isUnlimited(User $user): bool
    {
        return (bool) $this->planFor($user)?->is_unlimited;
    }

    public function limitFor(User $user, string $resource): ?PlanLimit
    {
        return $this->planFor($user)?->limits->firstWhere('resource', $resource);
    }

    /**
     * Uso atual do recurso pelo usuário. Nenhum recurso consumível (ex:
     * projects, uploads) existe ainda no módulo App, então isso retorna 0 até
     * que uma feature real passe a contabilizar consumo.
     */
    public function currentUsage(User $user, string $resource): int
    {
        return 0;
    }

    public function hasReachedLimit(User $user, string $resource): bool
    {
        if ($this->isUnlimited($user)) {
            return false;
        }

        $planLimit = $this->limitFor($user, $resource);

        if (! $planLimit) {
            return true;
        }

        if ($planLimit->limit === null) {
            return false;
        }

        return $this->currentUsage($user, $resource) >= $planLimit->limit;
    }

    public function canUseFeature(User $user, string $feature): bool
    {
        return ! $this->hasReachedLimit($user, $feature);
    }

    /**
     * @return array{status: string, started_at: \Illuminate\Support\Carbon, ends_at: ?\Illuminate\Support\Carbon, plan: Plan, limits: array<int, array{resource: string, limit: ?int, used: int, unlimited: bool}>}|null
     */
    public function summaryFor(User $user): ?array
    {
        $subscription = $this->activeSubscription($user);

        if (! $subscription) {
            return null;
        }

        $plan = $subscription->plan;

        return [
            'status' => $subscription->status,
            'started_at' => $subscription->started_at,
            'ends_at' => $subscription->ends_at,
            'plan' => $plan,
            'limits' => $plan->limits->map(fn (PlanLimit $limit) => [
                'resource' => $limit->resource,
                'limit' => $plan->is_unlimited ? null : $limit->limit,
                'used' => $plan->is_unlimited ? 0 : $this->currentUsage($user, $limit->resource),
                'unlimited' => $plan->is_unlimited || $limit->limit === null,
            ])->values()->all(),
        ];
    }
}
