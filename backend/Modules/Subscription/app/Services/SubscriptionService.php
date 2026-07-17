<?php

namespace Modules\Subscription\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Modules\Subscription\Contracts\SubscriptionServiceInterface;
use Modules\Subscription\Data\Payment;
use Modules\Subscription\Data\PaymentData;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\UserSubscription;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function createSubscription(User $user, Plan $plan): UserSubscription
    {
        return UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function cancelSubscription(UserSubscription $subscription): UserSubscription
    {
        $subscription->update([
            'status' => 'canceled',
            'ends_at' => now(),
        ]);

        return $subscription->fresh();
    }

    public function renewSubscription(UserSubscription $subscription): UserSubscription
    {
        $subscription->update([
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => null,
        ]);

        return $subscription->fresh();
    }

    public function changePlan(UserSubscription $subscription, Plan $newPlan): UserSubscription
    {
        $subscription->update([
            'plan_id' => $newPlan->id,
        ]);

        return $subscription->fresh();
    }

    /**
     * Sem gateway integrado ainda (ver docs/guia_projeto.md, Entrega 3) e
     * sem tabela de pagamentos no schema — por enquanto só registra o
     * evento em log, para não perder o dado enquanto a cobrança real não
     * existe. Quando um gateway for integrado, isso passa a persistir em
     * uma tabela `payments` própria.
     */
    public function registerPayment(UserSubscription $subscription, PaymentData $payment): Payment
    {
        $registeredAt = CarbonImmutable::now();

        Log::channel('stack')->info('Pagamento registrado (sem persistência ainda)', [
            'subscription_id' => $subscription->id,
            'amount_cents' => $payment->amountCents,
            'currency' => $payment->currency,
            'gateway_reference' => $payment->gatewayReference,
            'status' => $payment->status,
        ]);

        return new Payment(
            subscriptionId: $subscription->id,
            amountCents: $payment->amountCents,
            currency: $payment->currency,
            gatewayReference: $payment->gatewayReference,
            status: $payment->status,
            registeredAt: $registeredAt,
        );
    }
}
