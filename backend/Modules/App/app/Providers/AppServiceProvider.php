<?php

namespace Modules\App\Providers;

use Modules\App\Application\MembroApplication;
use Modules\App\Http\Controllers\MembroController;
use Modules\App\Interfaces\MembroApplicationInterface;
use Modules\App\Interfaces\MembroControllerInterface;
use Modules\App\Interfaces\MembroRepositoryInterface;
use Modules\App\Repositories\MembroRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AppServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'App';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'app';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

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
        $this->app->bind(MembroRepositoryInterface::class, MembroRepository::class);
        $this->app->bind(MembroApplicationInterface::class, MembroApplication::class);
        $this->app->bind(MembroControllerInterface::class, MembroController::class);

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
