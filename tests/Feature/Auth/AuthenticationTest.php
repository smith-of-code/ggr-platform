<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertStatus(302);

        $this->post('/logout');
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_authenticate_with_phone_in_various_formats(): void
    {
        $user = User::factory()->create([
            'phone' => '+7 (916) 111-22-33',
        ]);

        $this->post('/login', [
            'login' => '89161112233',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $this->post('/logout');
        $this->assertGuest();
    }

    public function test_phone_login_fails_when_normalized_phone_is_ambiguous(): void
    {
        User::factory()->create([
            'email' => 'dup-phone-a@test.local',
            'phone' => '+79161112233',
        ]);
        User::factory()->create([
            'email' => 'dup-phone-b@test.local',
            'phone' => '8 (916) 111-22-33',
        ]);

        $this->post('/login', [
            'login' => '89161112233',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
