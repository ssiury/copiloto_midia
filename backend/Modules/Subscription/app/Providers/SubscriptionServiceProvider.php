<?php

namespace Modules\Subscription\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Subscription\Console\Commands\MakeOwnerCommand;
use Modules\Subscription\Contracts\PlanServiceInterface;
use Modules\Subscription\Contracts\SubscriptionServiceInterface;
use Modules\Subscription\Services\PlanService;
use Modules\Subscription\Services\SubscriptionService;

class SubscriptionServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Subscription';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'subscription';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        MakeOwnerCommand::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(PlanServiceInterface::class, PlanService::class);
        $this->app->bind(SubscriptionServiceInterface::class, SubscriptionService::class);

        parent::register();
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
