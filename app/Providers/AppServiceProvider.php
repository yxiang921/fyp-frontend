<?php

namespace App\Providers;

use App\Models\MissingPerson;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        // Morph map for polymorphic relationships
        // Maps all reference types — including legacy POI — to MissingPerson
        Relation::morphMap([
            'missing_person' => MissingPerson::class,
            'App\\Models\\MissingPerson' => MissingPerson::class,
            'App\\Models\\PersonOfInterest' => MissingPerson::class,
        ]);
    }
}
