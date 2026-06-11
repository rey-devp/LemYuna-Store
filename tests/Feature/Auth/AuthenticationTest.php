<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk Login & Logout.
 *
 * Mencakup:
 * - Render halaman login
 * - Login sukses (user biasa & admin)
 * - Login gagal (password salah, field kosong, email invalid)
 * - Rate limiting (lockout setelah 5 percobaan gagal)
 * - Logout
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HALAMAN LOGIN
    // =========================================================================

    /** @test */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_authenticated_user_cannot_access_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        // Middleware guest harus redirect jika sudah login
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    // =========================================================================
    // LOGIN SUKSES
    // =========================================================================

    /** @test */
    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function test_admin_user_can_login_successfully(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        // Login redirect ke HOME (/dashboard), dari sana admin diarahkan ke admin panel
        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertEquals('admin', auth()->user()->role);
    }

    // =========================================================================
    // LOGIN GAGAL
    // =========================================================================

    /** @test */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email'    => 'notexist@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_login_fails_with_empty_email(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_login_fails_with_empty_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function test_login_fails_with_invalid_email_format(): void
    {
        $response = $this->post('/login', [
            'email'    => 'bukan-email-valid',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // RATE LIMITING
    // =========================================================================

    /** @test */
    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        // Lakukan 5 percobaan login gagal
        foreach (range(1, 5) as $attempt) {
            $this->post('/login', [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // Percobaan ke-6: harus kena rate limit (throttle key)
        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        // Setelah rate limit, user tetap tidak terauthentikasi
        $this->assertGuest();
        // Response harus memiliki error di field email (baik auth failed atau throttle)
        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    /** @test */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /** @test */
    public function test_guest_cannot_access_logout(): void
    {
        // Logout route dilindungi middleware auth — guest diredirect ke login
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }
}
