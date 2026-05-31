<?php

declare(strict_types=1);

namespace Tests\Feature\Distribution;

use Tests\TestCase;

final class DistributionStudioCopyTest extends TestCase
{
    public function test_updates_page_documents_owner_permission_requirement(): void
    {
        $root = dirname(base_path(), 2);
        $updatesPage = (string) file_get_contents(
            $root.'/apps/studio/src/features/updates/pages/UpdatesPage.tsx',
        );

        $this->assertStringContainsString('Owner permission required', $updatesPage);
    }

    public function test_plugins_page_warns_about_trusted_php(): void
    {
        $root = dirname(base_path(), 2);
        $pluginsPage = (string) file_get_contents(
            $root.'/apps/studio/src/features/plugins/pages/PluginsListPage.tsx',
        );

        $this->assertStringContainsString('trusted PHP', $pluginsPage);
    }
}
