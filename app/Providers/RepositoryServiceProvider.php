<?php

namespace App\Providers;

use App\Repositories\Contracts\CvRepositoryInterface;
use App\Repositories\Contracts\JobContactRepositoryInterface;
use App\Repositories\Contracts\JobReminderRepositoryInterface;
use App\Repositories\Contracts\TrackedJobRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentCvRepository;
use App\Repositories\Eloquent\EloquentJobContactRepository;
use App\Repositories\Eloquent\EloquentJobReminderRepository;
use App\Repositories\Eloquent\EloquentTrackedJobRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        TrackedJobRepositoryInterface::class => EloquentTrackedJobRepository::class,
        CvRepositoryInterface::class => EloquentCvRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
        JobReminderRepositoryInterface::class => EloquentJobReminderRepository::class,
        JobContactRepositoryInterface::class => EloquentJobContactRepository::class,
    ];

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
        //
    }
}
