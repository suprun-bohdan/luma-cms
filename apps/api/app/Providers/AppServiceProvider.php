<?php

namespace App\Providers;

use App\Modules\Content\Models\Collection;
use App\Modules\Content\Policies\CollectionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Collection::class, CollectionPolicy::class);
    }
}
