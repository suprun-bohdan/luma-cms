<?php

declare(strict_types=1);

namespace Tests\Feature\Distribution;

use Tests\TestCase;

final class DistributionArtifactsTest extends TestCase
{
    public function test_shared_hosting_artifacts_exist(): void
    {
        $root = dirname(base_path(), 2);

        $this->assertFileExists($root.'/INSTALL.txt');
        $this->assertFileExists($root.'/index.php');
        $this->assertFileExists($root.'/install.php');
        $this->assertFileExists($root.'/.htaccess');
        $this->assertFileExists($root.'/luma-requirements.php');
        $this->assertFileExists($root.'/apps/api/.env.shared.example');
        $this->assertFileExists($root.'/deploy/nginx/luma.conf');
        $this->assertFileExists($root.'/deploy/nginx/luma-root.conf.example');
        $this->assertFileExists($root.'/deploy/apache/luma.htaccess');
        $this->assertFileExists($root.'/docs/installation-validation.md');
    }

    public function test_shared_hosting_root_entry_routes_admin_and_blocks_apps(): void
    {
        $root = dirname(base_path(), 2);
        $htaccess = (string) file_get_contents($root.'/.htaccess');
        $index = (string) file_get_contents($root.'/index.php');
        $install = (string) file_get_contents($root.'/install.php');

        $this->assertStringContainsString("header('Location: /install.php'", $index);
        $this->assertStringContainsString('luma_serve_studio', $index);
        $this->assertStringContainsString('luma_bootstrap_api', $index);
        $this->assertStringNotContainsString("header('Location: install.php'", $index);
        $this->assertStringContainsString('/admin/setup', $install);
        $this->assertStringContainsString('^apps/', $htaccess);
        $this->assertStringContainsString('^admin/', $htaccess);
        $this->assertStringContainsString('index.php', $htaccess);
        $this->assertStringContainsString('install', $htaccess);
    }

    public function test_install_guide_uses_admin_setup_url(): void
    {
        $root = dirname(base_path(), 2);
        $installGuide = (string) file_get_contents($root.'/INSTALL.txt');

        $this->assertStringContainsString('/admin/setup', $installGuide);
        $this->assertStringContainsString('LUMA_SETUP_TOKEN', $installGuide);
        $this->assertStringContainsString('index.php', $installGuide);
        $this->assertStringContainsString('install.php', $installGuide);
        $this->assertStringContainsString('directly in your site folder', strtolower($installGuide));
    }

    public function test_release_script_packages_flat_zip_from_project_root(): void
    {
        $root = dirname(base_path(), 2);
        $scriptPath = dirname($root).'/scripts/release-shared.sh';

        if (! is_file($scriptPath)) {
            $this->markTestSkipped('Outer workspace release script not available in this checkout.');
        }

        $script = (string) file_get_contents($scriptPath);

        $this->assertStringContainsString('cd "$STAGING"', $script);
        $this->assertStringContainsString('zip -rq "$ARCHIVE" .', $script);
        $this->assertStringContainsString('ensure-writable-paths.sh', $script);
        $this->assertStringNotContainsString('deploy/shared-hosting-root', $script);
    }

    public function test_nginx_deploy_sample_serves_admin_and_redirects_studio(): void
    {
        $root = dirname(base_path(), 2);
        $nginx = (string) file_get_contents($root.'/deploy/nginx/luma.conf');

        $this->assertStringContainsString('location /admin/', $nginx);
        $this->assertStringContainsString('location /studio/', $nginx);
        $this->assertStringContainsString('return 301 /admin/', $nginx);
    }

    public function test_apache_deploy_sample_serves_admin_and_redirects_studio(): void
    {
        $root = dirname(base_path(), 2);
        $htaccess = (string) file_get_contents($root.'/deploy/apache/luma.htaccess');

        $this->assertStringContainsString('admin/', $htaccess);
        $this->assertStringContainsString('studio', $htaccess);
    }

    public function test_shared_env_example_documents_setup_token(): void
    {
        $envExample = (string) file_get_contents(base_path('.env.shared.example'));

        $this->assertStringContainsString('LUMA_SETUP_TOKEN', $envExample);
    }

    public function test_studio_vite_default_base_is_admin(): void
    {
        $root = dirname(base_path(), 2);
        $viteConfig = (string) file_get_contents($root.'/apps/studio/vite.config.ts');

        $this->assertStringContainsString("?? '/admin/'", $viteConfig);
    }

    public function test_studio_readme_documents_admin_base_path(): void
    {
        $root = dirname(base_path(), 2);
        $readme = (string) file_get_contents($root.'/apps/studio/README.md');

        $this->assertStringContainsString('VITE_BASE_PATH=/admin/', $readme);
        $this->assertStringContainsString('/admin/', $readme);
    }
}
