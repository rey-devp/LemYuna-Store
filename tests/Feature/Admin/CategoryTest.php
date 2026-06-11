<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk manajemen Kategori di panel Admin.
 *
 * Mencakup:
 * - ACL (akses hanya untuk admin)
 * - CRUD (buat, lihat, ubah, hapus)
 * - Sorting list (urutan by name)
 * - Validasi input
 */
class CategoryTest extends TestCase
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
    // ACL — Aturan Akses Role
    // =========================================================================

    /** @test */
    public function test_guest_cannot_access_category_list(): void
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_regular_user_cannot_access_category_list(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_access_category_list(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_guest_cannot_create_category(): void
    {
        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Kategori Baru',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('categories', ['name' => 'Kategori Baru']);
    }

    /** @test */
    public function test_regular_user_cannot_create_category(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->post(route('admin.categories.store'), [
            'name' => 'Kategori Baru',
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // CRUD — Create
    // =========================================================================

    /** @test */
    public function test_admin_can_view_create_form(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.categories.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_admin_can_create_category(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Game Top-Up',
            'icon' => '🎮',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', [
            'name' => 'Game Top-Up',
            'slug' => 'game-top-up',
            'icon' => '🎮',
        ]);
    }

    /** @test */
    public function test_admin_can_create_category_without_icon(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Kategori Tanpa Icon',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Tanpa Icon',
            'slug' => 'kategori-tanpa-icon',
            'icon' => null,
        ]);
    }

    /** @test */
    public function test_admin_cannot_create_duplicate_category_name(): void
    {
        $admin = $this->adminUser();
        Category::factory()->create(['name' => 'Streaming', 'slug' => 'streaming']);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Streaming',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 1);
    }

    // =========================================================================
    // CRUD — Read / List
    // =========================================================================

    /** @test */
    public function test_category_list_passes_categories_to_view(): void
    {
        $admin = $this->adminUser();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $this->assertCount(3, $response->viewData('categories'));
    }

    /** @test */
    public function test_categories_are_sorted_alphabetically_by_name(): void
    {
        $admin = $this->adminUser();
        Category::factory()->create(['name' => 'Zebra Category',  'slug' => 'zebra-category']);
        Category::factory()->create(['name' => 'Alpha Category',  'slug' => 'alpha-category']);
        Category::factory()->create(['name' => 'Middle Category', 'slug' => 'middle-category']);

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $categories = $response->viewData('categories');
        $this->assertEquals('Alpha Category',  $categories->get(0)->name);
        $this->assertEquals('Middle Category', $categories->get(1)->name);
        $this->assertEquals('Zebra Category',  $categories->get(2)->name);
    }

    /** @test */
    public function test_category_list_shows_product_count(): void
    {
        $admin    = $this->adminUser();
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $categories  = $response->viewData('categories');
        $found       = $categories->firstWhere('id', $category->id);
        $this->assertEquals(3, $found->products_count);
    }

    // =========================================================================
    // CRUD — Update
    // =========================================================================

    /** @test */
    public function test_admin_can_view_edit_form(): void
    {
        $admin    = $this->adminUser();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertViewHas('category', $category);
    }

    /** @test */
    public function test_admin_can_update_category(): void
    {
        $admin    = $this->adminUser();
        $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'New Name',
            'icon' => '📺',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', [
            'id'   => $category->id,
            'name' => 'New Name',
            'slug' => 'new-name',
            'icon' => '📺',
        ]);
    }

    /** @test */
    public function test_admin_can_update_category_with_same_name(): void
    {
        $admin    = $this->adminUser();
        $category = Category::factory()->create(['name' => 'Same Name', 'slug' => 'same-name']);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Same Name',
            'icon' => '⚡',
        ]);

        // Tidak boleh error unique karena exclude ID sendiri
        $response->assertSessionDoesntHaveErrors('name');
        $response->assertRedirect(route('admin.categories.index'));
    }

    // =========================================================================
    // CRUD — Delete
    // =========================================================================

    /** @test */
    public function test_admin_can_delete_category_without_products(): void
    {
        $admin    = $this->adminUser();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function test_admin_cannot_delete_category_that_has_products(): void
    {
        $admin    = $this->adminUser();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    // =========================================================================
    // VALIDASI INPUT
    // =========================================================================

    /** @test */
    public function test_category_name_is_required(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_category_name_cannot_exceed_255_characters(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_category_icon_cannot_exceed_50_characters(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Valid Name',
            'icon' => str_repeat('x', 51),
        ]);

        $response->assertSessionHasErrors('icon');
    }
}
