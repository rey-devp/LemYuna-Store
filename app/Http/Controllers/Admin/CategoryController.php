<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Inject CategoryRepository agar controller tidak langsung
     * bergantung pada Eloquent — lebih mudah di-test & di-maintain.
     */
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Tampilkan daftar kategori, diurutkan by name.
     */
    public function index()
    {
        $categories = $this->categoryRepository->allWithProductCount();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'icon' => 'nullable|string|max:50',
        ]);

        $this->categoryRepository->create([
            'name' => $request->name,
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori LumYena berhasil ditambahkan!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'icon' => 'nullable|string|max:50',
        ]);

        $this->categoryRepository->update($category, [
            'name' => $request->name,
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori LumYena berhasil diubah!');
    }

    public function destroy(Category $category)
    {
        $deleted = $this->categoryRepository->delete($category);

        if (! $deleted) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih menampung produk aktif.');
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori dihapus.');
    }
}
