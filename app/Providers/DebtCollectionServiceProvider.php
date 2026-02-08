<?php

// app/Providers/DebtCollectionServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DebtCollectionService;
use App\Repositories\DebtCollectionRepository;
use App\Contracts\DebtCollectionServiceInterface;

class DebtCollectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DebtCollectionServiceInterface::class, DebtCollectionService::class);
        $this->app->bind(DebtCollectionRepository::class);
    }

    public function boot(): void
    {
        // Cache configuration for debt collection
        $this->app['cache']->extend('debt_collection', function ($app) {
            return $app['cache']->store('redis')->tags(['debt_collection']);
        });
    }
}