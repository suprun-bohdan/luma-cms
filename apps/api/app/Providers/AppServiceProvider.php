<?php

namespace App\Providers;

use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use App\Modules\Content\Models\Field;
use App\Modules\Content\Policies\CollectionPolicy;
use App\Modules\Content\Policies\EntryPolicy;
use App\Modules\Content\Policies\FieldPolicy;
use App\Modules\Media\Models\Media;
use App\Modules\Media\Policies\MediaPolicy;
use App\Modules\Navigation\Models\Menu;
use App\Modules\Navigation\Policies\MenuPolicy;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Policies\PagePolicy;
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
        Gate::policy(Field::class, FieldPolicy::class);
        Gate::policy(Entry::class, EntryPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Menu::class, MenuPolicy::class);
    }
}
