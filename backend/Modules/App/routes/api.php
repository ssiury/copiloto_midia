<?php

use Illuminate\Support\Facades\Route;
use Modules\App\Http\Controllers\AppController;
use Modules\App\Http\Controllers\MembroController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('apps', AppController::class)->names('app');

    Route::get('membros', [MembroController::class, 'index'])->name('membros.index');
    Route::post('membros', [MembroController::class, 'store'])->name('membros.store');
    Route::put('membros/{id}', [MembroController::class, 'update'])->name('membros.update');
    Route::delete('membros/{id}', [MembroController::class, 'destroy'])->name('membros.destroy');
});
