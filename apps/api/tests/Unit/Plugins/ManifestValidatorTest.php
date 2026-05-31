<?php

declare(strict_types=1);

namespace Tests\Unit\Plugins;

use App\Modules\Plugins\Services\ManifestValidator;
use InvalidArgumentException;
use Tests\TestCase;

final class ManifestValidatorTest extends TestCase
{
    private ManifestValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ManifestValidator();
    }

    public function test_valid_manifest_passes_validation(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(dirname(__DIR__, 5).'/plugins/luma.demo/luma.plugin.json'),
            true,
        );

        $validated = $this->validator->validate($manifest);

        $this->assertSame('luma.demo', $validated['id']);
        $this->assertTrue($this->validator->isCompatible($validated));
    }

    public function test_missing_field_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate([
            'schemaVersion' => '1.0',
            'id' => 'broken.plugin',
        ]);
    }
}
