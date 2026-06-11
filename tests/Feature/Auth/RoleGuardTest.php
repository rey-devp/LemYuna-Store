<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk Middleware Auth & Role Guard.
 *
 * Mencakup:
 * - Middleware 'auth': proteksi route yang butuh login
 * - Middleware 'admin': proteksi route khusus admin
 * - Redirect yang benar untuk setiap skenario
 */
class RoleGuardTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function adminUser(): User
    {
        return User::factory()->admin()->create();
    }

    private function regularUser(): User
    {
        return User::factory()->regularUser()->create();
    }

    // =========================================================================
    // MIDDLEWARE 'auth' — Proteksi Route Butuh Login
    // =========================================================================

    /** @test */
    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    // =========================================================================
    // MIDDLEWARE 'admin' — Role Guard Admin Panel
    // =========================================================================

    /** @test */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_regular_user_cannot_access_admin_products(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get('/admin/products');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_access_admin_products(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_regular_user_cannot_access_admin_categories(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get('/admin/categories');

        $response->assertStatus(403);
    }

    // =========================================================================
    // REDIRECT BEHAVIOR
    // =========================================================================

    /** @test */
    public function test_admin_user_is_redirected_to_admin_panel_from_dashboard(): void
    {
        $admin = $this->adminUser();

        // Admin yang akses /dashboard harus di-redirect ke admin panel
        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    /** @test */
    public function test_regular_user_stays_on_dashboard(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get('/dashboard');

        // User biasa tidak di-redirect, langsung lihat dashboard
        $response->assertStatus(200);
    }

    // =========================================================================
    // ABORT 403 MESSAGE
    // =========================================================================

    /** @test */
    public function test_403_response_when_regular_user_accesses_admin_area(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
        $response->assertSeeText('Unauthorized access');
    }
}
