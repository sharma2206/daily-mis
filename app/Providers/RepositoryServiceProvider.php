<?php

namespace App\Providers;

use App\Repositories\Contracts\MisRepositoryInterface;
use App\Repositories\MisRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MisRepositoryInterface::class, MisRepository::class);
    }

    public function boot(): void {}
}
