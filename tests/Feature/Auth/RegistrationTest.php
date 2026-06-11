<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk Registrasi pengguna baru.
 *
 * Mencakup:
 * - Render halaman register
 * - Registrasi sukses dengan semua field
 * - Validasi: duplikat email, password tidak match
 * - Validasi: format whatsapp (harus diawali 62)
 * - Validasi: field wajib & batas karakter
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HALAMAN REGISTER
    // =========================================================================

    /** @test */
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    // =========================================================================
    // REGISTRASI SUKSES
    // =========================================================================

    /** @test */
    public function test_new_users_can_register_with_required_fields_only(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function test_new_users_can_register_with_all_fields(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Gamer Sejati',
            'email'                 => 'gamer@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'whatsapp'              => '6281234567890',
            'game_id'               => 'GamerID#1234',
            'streaming_username'    => 'gamersejati_tv',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertDatabaseHas('users', [
            'email'              => 'gamer@example.com',
            'game_id'            => 'GamerID#1234',
            'streaming_username' => 'gamersejati_tv',
            'whatsapp'           => '6281234567890',
        ]);
    }

    /** @test */
    public function test_new_user_gets_default_role_user(): void
    {
        $this->post('/register', [
            'name'                  => 'New User',
            'email'                 => 'newuser@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role'  => 'user',
        ]);
    }

    // =========================================================================
    // VALIDASI — Email
    // =========================================================================

    /** @test */
    public function test_registration_fails_with_duplicate_email(): void
    {
        // User pertama berhasil
        $this->post('/register', [
            'name'                  => 'User Pertama',
            'email'                 => 'sama@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->post('/logout');

        // User kedua dengan email yang sama harus gagal
        $response = $this->post('/register', [
            'name'                  => 'User Kedua',
            'email'                 => 'sama@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    /** @test */
    public function test_registration_fails_with_invalid_email_format(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'ini-bukan-email',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // =========================================================================
    // VALIDASI — Password
    // =========================================================================

    /** @test */
    public function test_registration_fails_when_password_not_confirmed(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function test_registration_fails_with_empty_password(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    // =========================================================================
    // VALIDASI — WhatsApp
    // =========================================================================

    /** @test */
    public function test_registration_fails_with_whatsapp_not_starting_with_62(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'whatsapp'              => '081234567890', // awalan 08, bukan 62
        ]);

        $response->assertSessionHasErrors('whatsapp');
        $this->assertGuest();
    }

    /** @test */
    public function test_registration_fails_with_whatsapp_too_short(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'whatsapp'              => '621234567', // hanya 9 digit setelah 62 (min 8)
        ]);

        $response->assertSessionHasErrors('whatsapp');
        $this->assertGuest();
    }

    /** @test */
    public function test_registration_succeeds_with_valid_whatsapp(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'whatsapp'              => '6281234567890', // valid: awalan 62 + min 8 digit
        ]);

        $response->assertSessionDoesntHaveErrors('whatsapp');
        $this->assertAuthenticated();
    }

    // =========================================================================
    // VALIDASI — Field Wajib
    // =========================================================================

    /** @test */
    public function test_registration_fails_with_empty_name(): void
    {
        $response = $this->post('/register', [
            'name'                  => '',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }
}
