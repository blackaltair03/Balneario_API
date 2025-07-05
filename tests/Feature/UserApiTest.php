<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function test_get_users_list()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)
        ->getJson('\api\users');

        $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email']
            ],
            'links',
            'meta'
        ]);
    }

    public function test_create_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
        ->postJson('\api\users', [
            'name' => '',
            'email' => 'invalid_email',
            'password' => 'short'
        ]);

        $response->assertStatus(422)
        ->assertJsonValidationErrors('name', 'email', 'password');
    }
}
