<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Repository untuk semua operasi data pada model Category.
 *
 * Memisahkan logika akses database dari Controller agar lebih mudah
 * di-test dan di-maintain (Repository Pattern).
 */
class CategoryRepository
{
    /**
     * Ambil semua kategori, diurutkan by name, beserta jumlah produknya.
     */
    public function allWithProductCount(): Collection
    {
        return Category::withCount('products')
            ->orderBy('name')
            ->get();
    }

    /**
     * Buat kategori baru. Slug di-generate otomatis dari name.
     */
    public function create(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);

        return Category::create($data);
    }

    /**
     * Update kategori yang sudah ada. Slug di-update otomatis dari name baru.
     */
    public function update(Category $category, array $data): bool
    {
        $data['slug'] = Str::slug($data['name']);

        return $category->update($data);
    }

    /**
     * Hapus kategori (hanya jika tidak punya produk aktif).
     * Return false jika masih ada produk.
     */
    public function delete(Category $category): bool
    {
        if ($this->hasProducts($category)) {
            return false;
        }

        $category->delete();

        return true;
    }

    /**
     * Cek apakah kategori masih memiliki produk.
     */
    public function hasProducts(Category $category): bool
    {
        return $category->products()->count() > 0;
    }

    /**
     * Temukan kategori berdasarkan ID.
     */
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }
}
