<?php

namespace Modules\Subscription\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Auth\Repositories\UserRepositoryInterface;
use Modules\Subscription\Contracts\SubscriptionServiceInterface;
use Modules\Subscription\Models\Plan;

class MakeOwnerCommand extends Command
{
    protected $signature = 'make:owner {email} {password}';

    protected $description = 'Cria um usuário owner com plano ilimitado (nunca disponível via cadastro público)';

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly SubscriptionServiceInterface $subscriptions,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email'], 'password' => ['required', 'string', 'min:8']],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        if ($this->users->findByEmail($email)) {
            $this->error("Já existe um usuário com o e-mail {$email}.");

            return self::FAILURE;
        }

        $ownerPlan = Plan::where('slug', 'owner')->first();

        if (! $ownerPlan) {
            $this->error('Plano "owner" não encontrado. Rode as migrations antes.');

            return self::FAILURE;
        }

        $user = $this->users->create([
            'name' => 'Owner',
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => 'owner',
        ]);

        $this->subscriptions->createSubscription($user, $ownerPlan);

        $this->info("Usuário owner criado com sucesso: {$email}");

        return self::SUCCESS;
    }
}
