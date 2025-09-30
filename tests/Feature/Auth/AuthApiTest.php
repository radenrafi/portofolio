<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_for_active_user(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'abilities',
                'user' => ['id', 'email', 'role', 'status'],
            ])
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_login_fails_for_inactive_user(): void
    {
        $user = User::factory()->teacher()->inactive()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Account is inactive.');
    }

    public function test_change_password_updates_hash_and_revokes_other_tokens(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['student']);

        $this->patchJson('/api/v1/auth/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }
}
