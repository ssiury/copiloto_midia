<?php

namespace Modules\Subscription\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Subscription\Contracts\SubscriptionServiceInterface;
use Modules\Subscription\Data\PaymentData;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\UserSubscription;

/**
 * Rotas serviço-a-serviço (protegidas por X-Internal-Key, middleware
 * "internal.key"), não por sessão de usuário — ver docs/architecture.md,
 * seção "Contrato de API interna por módulo".
 */
class InternalSubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptions,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'plan_id' => ['required', 'uuid', 'exists:plans,id'],
        ]);

        $user = \App\Models\User::findOrFail($data['user_id']);
        $plan = Plan::findOrFail($data['plan_id']);

        $subscription = $this->subscriptions->createSubscription($user, $plan);

        return response()->json([
            'data' => $subscription,
            'meta' => (object) [],
        ], 201);
    }

    public function cancel(UserSubscription $subscription): JsonResponse
    {
        $subscription = $this->subscriptions->cancelSubscription($subscription);

        return response()->json([
            'data' => $subscription,
            'meta' => (object) [],
        ]);
    }

    public function renew(UserSubscription $subscription): JsonResponse
    {
        $subscription = $this->subscriptions->renewSubscription($subscription);

        return response()->json([
            'data' => $subscription,
            'meta' => (object) [],
        ]);
    }

    public function changePlan(Request $request, UserSubscription $subscription): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', 'uuid', 'exists:plans,id'],
        ]);

        $newPlan = Plan::findOrFail($data['plan_id']);

        $subscription = $this->subscriptions->changePlan($subscription, $newPlan);

        return response()->json([
            'data' => $subscription,
            'meta' => (object) [],
        ]);
    }

    public function registerPayment(Request $request, UserSubscription $subscription): JsonResponse
    {
        $data = $request->validate([
            'amount_cents' => ['required', 'string'],
            'currency' => ['required', 'string'],
            'gateway_reference' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        $payment = $this->subscriptions->registerPayment($subscription, new PaymentData(
            amountCents: $data['amount_cents'],
            currency: $data['currency'],
            gatewayReference: $data['gateway_reference'],
            status: $data['status'],
        ));

        return response()->json([
            'data' => $payment,
            'meta' => (object) [],
        ], 201);
    }
}
