<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
    }
}
