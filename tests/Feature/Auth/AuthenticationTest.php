<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_remember_email_cookie_is_set_when_remember_is_checked(): void
    {
        $user = User::factory()->create();

        \Illuminate\Support\Facades\Cookie::spy();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->set('form.remember', true)
            ->assertSet('form.remember', true);

        $component->call('login');

        $component->assertHasNoErrors();
        
        // Assert the remember_email cookie was queued with the correct parameters
        \Illuminate\Support\Facades\Cookie::shouldHaveReceived('queue')
            ->with('remember_email', $user->email, 43200);
    }

    public function test_remember_email_cookie_is_cleared_when_remember_is_unchecked(): void
    {
        $user = User::factory()->create();

        \Illuminate\Support\Facades\Cookie::spy();

        // Simulate visiting with cookie set previously
        $this->withCookie('remember_email', \Illuminate\Support\Facades\Crypt::encryptString($user->email));

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->set('form.remember', false);

        $component->call('login');

        $component->assertHasNoErrors();

        // Assert the remember_email cookie was cleared (queued with a forget/expired state)
        \Illuminate\Support\Facades\Cookie::shouldHaveReceived('queue')
            ->with('remember_email', '', -1);
    }
}
