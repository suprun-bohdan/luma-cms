<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Modules\Media\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class MediaApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        Storage::fake('public');
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/v1/media');

        $response->assertUnauthorized();
    }

    public function test_lists_media(): void
    {
        Media::factory()->count(2)->create();

        $response = $this->getJson(
            '/api/v1/media',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    [
                        'uuid',
                        'filename',
                        'mime_type',
                        'size',
                        'url',
                        'variants',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_uploads_image(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->post(
            '/api/v1/media',
            [
                'file' => $file,
                'alt_text' => 'Hero image',
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.filename', 'photo.jpg')
            ->assertJsonPath('data.alt_text', 'Hero image')
            ->assertJsonPath('data.mime_type', 'image/jpeg');

        $uuid = $response->json('data.uuid');
        $this->assertIsString($uuid);
        Storage::disk('public')->assertExists('media/'.$uuid.'/photo.jpg');
    }

    public function test_rejects_invalid_mime_on_upload(): void
    {
        $file = UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream');

        $response = $this->post(
            '/api/v1/media',
            ['file' => $file],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }

    public function test_shows_media_by_uuid(): void
    {
        $media = Media::factory()->create([
            'filename' => 'cover.png',
            'mime_type' => 'image/png',
        ]);

        $response = $this->getJson(
            '/api/v1/media/'.$media->uuid,
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.uuid', $media->uuid)
            ->assertJsonPath('data.filename', 'cover.png');
    }

    public function test_updates_alt_text(): void
    {
        $media = Media::factory()->create(['alt_text' => null]);

        $response = $this->putJson(
            '/api/v1/media/'.$media->uuid,
            ['alt_text' => 'Accessible label'],
            $this->withBearer($this->editorUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.alt_text', 'Accessible label');
    }

    public function test_deletes_media_as_admin(): void
    {
        $media = Media::factory()->create();
        Storage::disk('public')->put($media->path, 'binary');

        $response = $this->deleteJson(
            '/api/v1/media/'.$media->uuid,
            [],
            $this->withBearer($this->adminUser()),
        );

        $response->assertNoContent();
        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }

    public function test_editor_cannot_delete_media(): void
    {
        $media = Media::factory()->create();

        $response = $this->deleteJson(
            '/api/v1/media/'.$media->uuid,
            [],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_public_media_show(): void
    {
        $media = Media::factory()->create();

        $response = $this->getJson('/api/v1/public/media/'.$media->uuid);

        $response
            ->assertOk()
            ->assertJsonPath('data.uuid', $media->uuid);
    }
}
