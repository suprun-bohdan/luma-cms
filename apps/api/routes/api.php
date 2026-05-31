<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\SystemController;
use App\Http\Controllers\Api\V1\SystemRequirementsController;
use App\Modules\Setup\Http\Controllers\Api\V1\SetupController;
use App\Modules\Auth\Http\Controllers\Api\V1\AuthController;
use App\Modules\Content\Http\Controllers\Api\V1\CollectionController;
use App\Modules\Content\Http\Controllers\Api\V1\EntryController;
use App\Modules\Content\Http\Controllers\Api\V1\FieldController;
use App\Modules\Content\Http\Controllers\Api\V1\PublicEntryController;
use App\Modules\Media\Http\Controllers\Api\V1\MediaController;
use App\Modules\Media\Http\Controllers\Api\V1\PublicMediaController;
use App\Modules\Navigation\Http\Controllers\Api\V1\MenuController;
use App\Modules\Navigation\Http\Controllers\Api\V1\PublicMenuController;
use App\Modules\Pages\Http\Controllers\Api\V1\EditorBlockTypesController;
use App\Modules\Pages\Http\Controllers\Api\V1\PageController;
use App\Modules\Pages\Http\Controllers\Api\V1\PagePreviewController;
use App\Modules\Pages\Http\Controllers\Api\V1\PublicPageController;
use App\Modules\Forms\Http\Controllers\Api\V1\FormController;
use App\Modules\Integrations\Http\Controllers\Api\V1\IntegrationAccessController;
use App\Modules\Integrations\Http\Controllers\Api\V1\IntegrationTokenController;
use App\Modules\Integrations\Http\Controllers\Api\V1\WebhookController;
use App\Modules\Integrations\Http\Controllers\Api\V1\WebhookDeliveryController;
use App\Modules\Plugins\Http\Controllers\Api\V1\AdminNavigationController;
use App\Modules\Plugins\Http\Controllers\Api\V1\PluginController;
use App\Modules\Seo\Http\Controllers\Api\V1\RedirectController;
use App\Modules\Settings\Http\Controllers\Api\V1\SettingsController;
use App\Modules\Onboarding\Http\Controllers\Api\V1\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);
    Route::get('/system/requirements', SystemRequirementsController::class);
    Route::get('/system/version', [SystemController::class, 'version']);

    Route::get('/setup/status', [SetupController::class, 'status']);

    Route::prefix('setup')->middleware('luma.not_installed')->group(function (): void {
        Route::get('/requirements', [SetupController::class, 'requirements']);
        Route::get('/logs', [SetupController::class, 'logs']);
        Route::post('/database/test', [SetupController::class, 'testDatabase']);
        Route::post('/database', [SetupController::class, 'saveDatabase']);
        Route::post('/finish', [SetupController::class, 'finish']);
    });

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::prefix('public')->group(function (): void {
        Route::get('/collections/{collection}/entries', [PublicEntryController::class, 'index']);
        Route::get('/entries/{entry}', [PublicEntryController::class, 'show']);
        Route::get('/media/{media:uuid}', [PublicMediaController::class, 'show']);
        Route::get('/pages/{page:slug}', [PublicPageController::class, 'show']);
        Route::get('/menus/{menu:slug}', [PublicMenuController::class, 'show']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::apiResource('collections', CollectionController::class);
        Route::apiResource('collections.fields', FieldController::class)
            ->scoped([
                'collection' => 'slug',
                'field' => 'slug',
            ]);

        Route::get('/collections/{collection}/entries', [EntryController::class, 'index']);
        Route::post('/collections/{collection}/entries', [EntryController::class, 'store']);
        Route::get('/entries/{entry}', [EntryController::class, 'show']);
        Route::put('/entries/{entry}', [EntryController::class, 'update']);
        Route::delete('/entries/{entry}', [EntryController::class, 'destroy']);
        Route::post('/entries/{entry}/publish', [EntryController::class, 'publish']);
        Route::post('/entries/{entry}/unpublish', [EntryController::class, 'unpublish']);

        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media', [MediaController::class, 'store']);
        Route::get('/media/{media:uuid}', [MediaController::class, 'show']);
        Route::put('/media/{media:uuid}', [MediaController::class, 'update']);
        Route::delete('/media/{media:uuid}', [MediaController::class, 'destroy']);

        Route::get('/pages', [PageController::class, 'index']);
        Route::post('/pages', [PageController::class, 'store']);
        Route::get('/pages/{page:slug}', [PageController::class, 'show']);
        Route::put('/pages/{page:slug}', [PageController::class, 'update']);
        Route::delete('/pages/{page:slug}', [PageController::class, 'destroy']);
        Route::post('/pages/{page:slug}/publish', [PageController::class, 'publish']);
        Route::post('/pages/{page:slug}/unpublish', [PageController::class, 'unpublish']);
        Route::post('/pages/preview-html', [PagePreviewController::class, 'draft']);
        Route::get('/pages/{page:slug}/preview-html', [PagePreviewController::class, 'show']);
        Route::post('/pages/{page:slug}/preview-html', [PagePreviewController::class, 'store']);

        Route::get('/editor/block-types', [EditorBlockTypesController::class, 'index']);

        Route::get('/menus', [MenuController::class, 'index']);
        Route::post('/menus', [MenuController::class, 'store']);
        Route::get('/menus/{menu:slug}', [MenuController::class, 'show']);
        Route::put('/menus/{menu:slug}', [MenuController::class, 'update']);
        Route::delete('/menus/{menu:slug}', [MenuController::class, 'destroy']);

        Route::get('/redirects', [RedirectController::class, 'index']);
        Route::post('/redirects', [RedirectController::class, 'store']);
        Route::get('/redirects/{redirect}', [RedirectController::class, 'show']);
        Route::put('/redirects/{redirect}', [RedirectController::class, 'update']);
        Route::delete('/redirects/{redirect}', [RedirectController::class, 'destroy']);

        Route::get('/settings', [SettingsController::class, 'show']);
        Route::patch('/settings', [SettingsController::class, 'update']);

        Route::get('/system/update/check', [SystemController::class, 'updateCheck']);
        Route::post('/system/update/run', [SystemController::class, 'updateRun']);

        Route::get('/onboarding/progress', [OnboardingController::class, 'progress']);
        Route::get('/onboarding/journal', [OnboardingController::class, 'journal']);
        Route::post('/onboarding/welcome', [OnboardingController::class, 'welcome']);
        Route::post('/onboarding/site-type', [OnboardingController::class, 'siteType']);
        Route::post('/onboarding/starter', [OnboardingController::class, 'starter']);
        Route::post('/onboarding/integrations', [OnboardingController::class, 'integrations']);
        Route::post('/onboarding/finish', [OnboardingController::class, 'finish']);

        Route::get('/forms', [FormController::class, 'index']);
        Route::post('/forms', [FormController::class, 'store']);
        Route::get('/forms/{form:slug}', [FormController::class, 'show']);
        Route::put('/forms/{form:slug}', [FormController::class, 'update']);
        Route::delete('/forms/{form:slug}', [FormController::class, 'destroy']);
        Route::get('/forms/{form:slug}/submissions', [FormController::class, 'submissions']);

        Route::get('/plugins/discover', [PluginController::class, 'discover']);
        Route::post('/plugins/install', [PluginController::class, 'install']);
        Route::get('/plugins', [PluginController::class, 'index']);
        Route::post('/plugins/{plugin:plugin_id}/enable', [PluginController::class, 'enable']);
        Route::post('/plugins/{plugin:plugin_id}/disable', [PluginController::class, 'disable']);
        Route::post('/plugins/{plugin:plugin_id}/capabilities/approve', [PluginController::class, 'approveCapability']);
        Route::delete('/plugins/{plugin:plugin_id}', [PluginController::class, 'destroy']);
        Route::get('/audit-logs', [PluginController::class, 'auditLogs']);

        Route::get('/admin/navigation-items', [AdminNavigationController::class, 'index']);

        Route::get('/integrations/webhooks/events', [WebhookController::class, 'events']);
        Route::get('/integrations/webhooks', [WebhookController::class, 'index']);
        Route::post('/integrations/webhooks', [WebhookController::class, 'store']);
        Route::get('/integrations/webhooks/{webhook}', [WebhookController::class, 'show']);
        Route::put('/integrations/webhooks/{webhook}', [WebhookController::class, 'update']);
        Route::delete('/integrations/webhooks/{webhook}', [WebhookController::class, 'destroy']);
        Route::get('/integrations/webhooks/{webhook}/deliveries', [WebhookDeliveryController::class, 'index']);
        Route::post('/integrations/webhooks/{webhook}/deliveries/{delivery}/retry', [WebhookDeliveryController::class, 'retry']);

        Route::get('/integrations/tokens/scopes', [IntegrationTokenController::class, 'scopes']);
        Route::get('/integrations/tokens', [IntegrationTokenController::class, 'index']);
        Route::post('/integrations/tokens', [IntegrationTokenController::class, 'store']);
        Route::delete('/integrations/tokens/{integrationToken}', [IntegrationTokenController::class, 'destroy']);
    });

    Route::prefix('integration')->middleware(['integration.token'])->group(function (): void {
        Route::middleware('integration.scope:content_read')->group(function (): void {
            Route::get('/pages/{page:slug}', [IntegrationAccessController::class, 'showPage']);
            Route::get('/collections/{collection:slug}/entries', [IntegrationAccessController::class, 'listEntries']);
            Route::get('/entries/{entry}', [IntegrationAccessController::class, 'showEntry']);
        });

        Route::middleware('integration.scope:media_read')->group(function (): void {
            Route::get('/media/{media:uuid}', [IntegrationAccessController::class, 'showMedia']);
        });

        Route::middleware('integration.scope:forms_read_submissions')->group(function (): void {
            Route::get('/forms/{form:slug}/submissions', [IntegrationAccessController::class, 'formSubmissions']);
        });
    });
});
