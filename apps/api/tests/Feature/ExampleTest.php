<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_web_root_redirects_to_setup_before_installation(): void
    {
        $this->get('/')
            ->assertRedirect('/admin/setup');
    }
}
