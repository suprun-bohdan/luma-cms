<?php

declare(strict_types=1);

namespace Tests\Feature\Forms;

use App\Modules\Forms\Database\Seeders\ContactFormSeeder;
use App\Modules\Forms\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FormSubmitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ContactFormSeeder::class);
    }

    public function test_valid_submit_stores_submission(): void
    {
        $response = $this->post('/public/forms/contact/submit', [
            '_token' => csrf_token(),
            '_hp' => '',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'message' => 'Hello from the contact form.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('form_submissions', [
            'form_id' => Form::query()->where('slug', 'contact')->value('id'),
        ]);

        $submission = Form::query()->where('slug', 'contact')->firstOrFail()
            ->submissions()->firstOrFail();

        $this->assertSame('Ada Lovelace', $submission->data['name'] ?? null);
    }

    public function test_honeypot_discards_submission(): void
    {
        $response = $this->post('/public/forms/contact/submit', [
            '_token' => csrf_token(),
            '_hp' => 'bot-value',
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'message' => 'Buy now',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_inactive_form_returns_not_found(): void
    {
        Form::query()->where('slug', 'contact')->update(['is_active' => false]);

        $response = $this->post('/public/forms/contact/submit', [
            '_token' => csrf_token(),
            '_hp' => '',
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello',
        ]);

        $response->assertNotFound();
    }

    public function test_missing_required_field_returns_validation_error(): void
    {
        $response = $this->post('/public/forms/contact/submit', [
            '_token' => csrf_token(),
            '_hp' => '',
            'name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('form_submissions', 0);
    }
}
