@extends('layouts.app', ['title' => 'Tambah Vendor'])

@section('content')
<form method="post" action="{{ route('vendors.store') }}" class="space-y-4">
    @csrf
    <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80 p-6">
        <h2 class="text-lg font-semibold mb-4">Data Vendor</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2" required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Telepon <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2" required>
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Tipe Vendor <span class="text-red-500">*</span></label>
                <select name="vendor_type" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2" required>
                    <option value="">Pilih Tipe</option>
                    <option value="trucking" @selected(old('vendor_type')=='trucking')>Trucking</option>
                    <option value="freight_forwarder" @selected(old('vendor_type')=='freight_forwarder')>Freight Forwarder</option>
                    <option value="supplier" @selected(old('vendor_type')=='supplier')>Supplier</option>
                    <option value="other" @selected(old('vendor_type')=='other')>Lainnya</option>
                </select>
                @error('vendor_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="rounded border-slate-300 dark:border-slate-700" @checked(old('is_active', true))>
                    <label for="is_active" class="text-sm">Aktif</label>
                </div>
                @error('is_active')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-4">
            <label class="block text-sm font-medium mb-1">Alamat</label>
            <textarea name="address" rows="3" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2">{{ old('address') }}</textarea>
            @error('address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('vendors.index') }}" class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Simpan</button>
    </div>
</form>
@endsection

