<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Garante que os planos free/pro/owner sempre existam após rodar as
 * migrations, sem exigir um passo manual de seed (o cadastro público
 * depende do plano Free existir para atribuir a assinatura automática).
 */
return new class extends Migration
{
    private const RESOURCE_LIMITS = [
        'free' => ['projects' => 3, 'uploads' => 10],
        'pro' => [],
        'owner' => [],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Free é o único plano com limites numéricos reais; Pro (pago) e
        // Owner (interno) são ambos ilimitados.
        $plans = [
            ['slug' => 'free', 'name' => 'Free', 'is_paid' => false, 'is_unlimited' => false],
            ['slug' => 'pro', 'name' => 'Pro', 'is_paid' => true, 'is_unlimited' => true],
            ['slug' => 'owner', 'name' => 'Owner', 'is_paid' => false, 'is_unlimited' => true],
        ];

        $now = now();

        foreach ($plans as $plan) {
            $planId = (string) Str::uuid();

            DB::table('plans')->insert([
                'id' => $planId,
                'name' => $plan['name'],
                'slug' => $plan['slug'],
                'is_paid' => $plan['is_paid'],
                'is_unlimited' => $plan['is_unlimited'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (self::RESOURCE_LIMITS[$plan['slug']] as $resource => $limit) {
                DB::table('plan_limits')->insert([
                    'id' => (string) Str::uuid(),
                    'plan_id' => $planId,
                    'resource' => $resource,
                    'limit' => $limit,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plan_limits')->whereIn('plan_id', function ($query) {
            $query->select('id')->from('plans')->whereIn('slug', ['free', 'pro', 'owner']);
        })->delete();

        DB::table('plans')->whereIn('slug', ['free', 'pro', 'owner'])->delete();
    }
};
