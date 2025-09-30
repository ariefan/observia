<?php

namespace App\Providers;

use App\Http\Middleware\TeamsPermission;
use App\Models\LivestockMilking;
use App\Models\LivestockWeight;
use App\Observers\LivestockMilkingObserver;
use App\Observers\LivestockWeightObserver;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var Kernel $kernel */
        $kernel = app()->make(Kernel::class);

        $kernel->addToMiddlewarePriorityBefore(
            SubstituteBindings::class,
            TeamsPermission::class,
        );

        // Register observers
        LivestockMilking::observe(LivestockMilkingObserver::class);
        LivestockWeight::observe(LivestockWeightObserver::class);
    }
}
