<?php

declare(strict_types=1);

namespace Tests\Feature\Forms;

use App\Modules\Forms\Database\Seeders\ContactFormSeeder;
use App\Modules\Forms\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class FormApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_unauthenticated_list_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/forms');

        $response->assertUnauthorized();
    }

    public function test_admin_creates_form_with_fields(): void
    {
        $response = $this->postJson(
            '/api/v1/forms',
            [
                'name' => 'Newsletter',
                'slug' => 'newsletter',
                'fields' => [
                    [
                        'name' => 'email',
                        'label' => 'Email',
                        'type' => 'email',
                        'required' => true,
                    ],
                ],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('slug', 'newsletter')
            ->assertJsonCount(1, 'fields');

        $this->assertDatabaseHas('forms', ['slug' => 'newsletter']);
        $this->assertDatabaseHas('form_fields', ['name' => 'email']);
    }

    public function test_admin_lists_forms(): void
    {
        $this->seed(ContactFormSeeder::class);

        $response = $this->getJson(
            '/api/v1/forms',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'contact');
    }

    public function test_admin_lists_submissions(): void
    {
        $this->seed(ContactFormSeeder::class);

        $form = Form::query()->where('slug', 'contact')->firstOrFail();
        $form->submissions()->create([
            'data' => ['name' => 'Ada', 'email' => 'ada@example.com', 'message' => 'Hello'],
        ]);

        $response = $this->getJson(
            '/api/v1/forms/contact/submissions',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.data.name', 'Ada');
    }

    public function test_editor_cannot_create_form(): void
    {
        $response = $this->postJson(
            '/api/v1/forms',
            [
                'name' => 'Blocked',
                'slug' => 'blocked',
            ],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();
    }
}
