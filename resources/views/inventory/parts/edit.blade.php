@extends('layouts.app', ['title' => 'Edit Sparepart'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">Edit Sparepart</h1>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $part->code }}</p>
                </div>
                <x-button :href="route('parts.index')" variant="ghost" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="hidden sm:inline">Batal</span>
                </x-button>
            </div>
        </x-slot:header>

        <form method="POST" action="{{ route('parts.update', $part) }}" class="space-y-4 md:space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Kode Part <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="code" 
                        value="{{ old('code', $part->code) }}" 
                        required
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Nama Part <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $part->name) }}" 
                        required
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Satuan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="unit" 
                        required
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="pcs" @selected(old('unit', $part->unit) === 'pcs')>Pcs</option>
                        <option value="unit" @selected(old('unit', $part->unit) === 'unit')>Unit</option>
                        <option value="liter" @selected(old('unit', $part->unit) === 'liter')>Liter</option>
                        <option value="kg" @selected(old('unit', $part->unit) === 'kg')>Kg</option>
                        <option value="set" @selected(old('unit', $part->unit) === 'set')>Set</option>
                    </select>
                    @error('unit')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Kategori
                    </label>
                    <input 
                        type="text" 
                        name="category" 
                        value="{{ old('category', $part->category) }}" 
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('category')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Minimum Stok
                    </label>
                    <input 
                        type="number" 
                        step="0.01"
                        name="min_stock" 
                        value="{{ old('min_stock', $part->min_stock) }}" 
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    @error('min_stock')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Status
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1"
                            @checked(old('is_active', $part->is_active))
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Deskripsi
                </label>
                <textarea 
                    name="description" 
                    rows="3"
                    class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >{{ old('description', $part->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2d2d2d]">
                <x-button :href="route('parts.index')" variant="outline" class="w-full sm:w-auto">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" class="w-full sm:w-auto">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

