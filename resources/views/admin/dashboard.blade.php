@extends('admin.layout')

@section('content')
    
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-lumyena-primary drop-shadow-sm mb-1">Rangkuman Dasbor 📊</h1>
            <p class="text-lumyena-muted font-bold text-sm">Kelola ekosistem LumYena Store </p>
        </div>
    </div>

    <!-- Stat Cards (Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Card 1: Kategori -->
        <div class="bg-white rounded-3xl border-4 border-lumyena-secondary shadow-[0_8px_0_#FFC0CB] p-6 relative overflow-hidden group hover:-translate-y-1 hover:border-lumyena-primary transition-all duration-300">
            <div class="absolute -right-6 -bottom-6 text-8xl opacity-10 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">
                📁
            </div>
            <div class="relative z-10" x-data="{ count: 0 }" x-init="let end = {{ $categoriesCount }}; let start = 0; let duration = 1500; let step = Math.ceil(end/(duration/16)); let timer = setInterval(() => { start += step; if(start >= end) { count = end; clearInterval(timer); } else { count = start; } }, 16);">
                <p class="text-sm font-extrabold text-lumyena-muted uppercase tracking-wider mb-1">Total Kategori</p>
                <h3 class="text-5xl font-black text-lumyena-text mb-2" x-text="count">{{ $categoriesCount }}</h3>
                <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center text-sm font-bold text-lumyena-primary hover:text-lumyena-hover">
                    Kelola Kategori <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>

        <!-- Card 2: Semua Produk -->
        <div class="bg-white rounded-3xl border-4 border-[#00d2d3]/30 shadow-[0_8px_0_rgba(0,210,211,0.2)] p-6 relative overflow-hidden group hover:-translate-y-1 hover:border-[#00d2d3] transition-all duration-300">
             <div class="absolute -right-6 -bottom-6 text-8xl opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-transform duration-500">
                🛍️
            </div>
            <div class="relative z-10" x-data="{ count: 0 }" x-init="let end = {{ $productsCount }}; let start = 0; let duration = 1500; let step = Math.ceil(end/(duration/16)); let timer = setInterval(() => { start += step; if(start >= end) { count = end; clearInterval(timer); } else { count = start; } }, 16);">
                <p class="text-sm font-extrabold text-[#00b894] uppercase tracking-wider mb-1">Total Produk (Keseluruhan)</p>
                <h3 class="text-5xl font-black text-lumyena-text mb-2" x-text="count">{{ $productsCount }}</h3>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center text-sm font-bold text-[#00d2d3] hover:text-[#0abde3]">
                    Kelola Produk <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>

        <!-- Card 3: Produk Aktif -->
        <div class="bg-white rounded-3xl border-4 border-green-200 shadow-[0_8px_0_#bbf7d0] p-6 relative overflow-hidden group hover:-translate-y-1 hover:border-green-400 transition-all duration-300">
             <div class="absolute -right-6 -bottom-6 text-8xl opacity-10 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">
                ✅
            </div>
            <div class="relative z-10" x-data="{ count: 0 }" x-init="let end = {{ $activeProductsCount }}; let start = 0; let duration = 1500; let step = Math.ceil(end/(duration/16)); let timer = setInterval(() => { start += step; if(start >= end) { count = end; clearInterval(timer); } else { count = start; } }, 16);">
                <p class="text-sm font-extrabold text-green-600 uppercase tracking-wider mb-1">Produk Dijual (Aktif)</p>
                <h3 class="text-5xl font-black text-lumyena-text mb-2" x-text="count">{{ $activeProductsCount }}</h3>
                <p class="text-xs font-bold text-lumyena-muted pt-1 border-t-2 border-green-100 mt-2">Item terpasang di etalase User</p>
            </div>
        </div>

    </div>
    
    <!-- Welcome Footer -->
    <div class="bg-white rounded-3xl border-4 border-lumyena-primary/30 p-8 shadow-sm flex flex-col items-center justify-center text-center">
        <span class="text-5xl mb-4">🚀</span>
        <h2 class="text-2xl font-black text-lumyena-primary mb-2">Semua Sistem Terhubung!</h2>
        <p class="text-lumyena-muted font-bold max-w-xl">Pilih menu di sisi bar kiri untuk menambah diskon, mengganti status jualan (aktif/tidak aktif), atau memberikan judul unik di platform toko Pink milik Anda.</p>
    </div>

@endsection
