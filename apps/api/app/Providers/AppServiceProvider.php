<?php

namespace App\Providers;

use App\Core\Install\ProductionPasswordGuard;
use App\Core\Install\InstallService;
use App\Core\Requirements\EnvironmentRequirementChecker;
use App\Core\System\Models\SystemUpdate;
use App\Core\System\Policies\SystemUpdatePolicy;
use App\Core\Update\SystemVersionService;
use App\Core\Update\UpdateService;
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
use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Policies\FormPolicy;
use App\Modules\Integrations\Models\IntegrationToken;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Policies\IntegrationTokenPolicy;
use App\Modules\Integrations\Policies\WebhookPolicy;
use App\Modules\Plugins\Models\AuditLog;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Policies\AuditLogPolicy;
use App\Modules\Plugins\Policies\PluginPolicy;
use App\Modules\Plugins\Services\AdminNavigationRegistry;
use App\Modules\Plugins\Services\BlockTypeRegistry;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use App\Modules\Plugins\Services\PluginRouteRegistry;
use App\Modules\Plugins\Services\PluginRouteRegistrar;
use App\Modules\Plugins\Services\PluginRuntimeService;
use App\Modules\Seo\Models\Redirect;
use App\Modules\Seo\Policies\RedirectPolicy;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Policies\SettingPolicy;
use App\Modules\Setup\Models\SetupLog;
use App\Modules\Setup\Policies\SetupLogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EnvironmentRequirementChecker::class, static fn (): EnvironmentRequirementChecker => EnvironmentRequirementChecker::default());
        $this->app->singleton(InstallService::class);
        $this->app->singleton(ProductionPasswordGuard::class);
        $this->app->singleton(UpdateService::class);
        $this->app->singleton(SystemVersionService::class);
        $this->app->singleton(ExtensionPointDispatcher::class);
        $this->app->singleton(AdminNavigationRegistry::class);
        $this->app->singleton(BlockTypeRegistry::class);
        $this->app->singleton(PluginRouteRegistry::class);
    }

    public function boot(): void
    {
        RateLimiter::for('setup', static fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('setup-write', static fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        Gate::policy(Collection::class, CollectionPolicy::class);
        Gate::policy(Field::class, FieldPolicy::class);
        Gate::policy(Entry::class, EntryPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(Redirect::class, RedirectPolicy::class);
        Gate::policy(Form::class, FormPolicy::class);
        Gate::policy(Webhook::class, WebhookPolicy::class);
        Gate::policy(IntegrationToken::class, IntegrationTokenPolicy::class);
        Gate::policy(Plugin::class, PluginPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(SystemUpdate::class, SystemUpdatePolicy::class);
        Gate::policy(SetupLog::class, SetupLogPolicy::class);

        if ($this->shouldBootPlugins()) {
            $this->app->booted(function (): void {
                $this->app->make(PluginRuntimeService::class)->bootEnabledPlugins();
            });
        }

        $this->app->booted(function (): void {
            $this->app->make(PluginRouteRegistrar::class)->registerRoutes();
        });
    }

    private function shouldBootPlugins(): bool
    {
        try {
            return Schema::hasTable('plugins');
        } catch (\Throwable) {
            // Database may be unavailable during composer scripts or pre-install bootstrap.
            return false;
        }
    }
}
