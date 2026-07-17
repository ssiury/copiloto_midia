<?php

use Illuminate\Support\Facades\Route;
use Modules\Subscription\Http\Controllers\InternalSubscriptionController;
use Modules\Subscription\Http\Controllers\SubscriptionController;

Route::middleware(['auth:sanctum'])->prefix('v1/subscription')->name('subscription.')->group(function () {
    Route::get('/me', [SubscriptionController::class, 'me'])->name('me');
});

// Rotas serviço-a-serviço: protegidas por X-Internal-Key em vez de sessão de
// usuário. É o contrato que outro serviço chamaria via HTTP quando o módulo
// Subscription for extraído do monólito — ver docs/architecture.md.
Route::middleware(['internal.key'])->prefix('v1/internal/subscriptions')->name('subscription.internal.')->group(function () {
    Route::post('/', [InternalSubscriptionController::class, 'store'])->name('store');
    Route::post('/{subscription}/cancel', [InternalSubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{subscription}/renew', [InternalSubscriptionController::class, 'renew'])->name('renew');
    Route::post('/{subscription}/change-plan', [InternalSubscriptionController::class, 'changePlan'])->name('change-plan');
    Route::post('/{subscription}/payments', [InternalSubscriptionController::class, 'registerPayment'])->name('payments');
});
