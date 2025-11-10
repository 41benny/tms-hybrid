@extends('layouts.app', ['title' => 'Tambah Customer'])

@section('content')
<form method="post" action="{{ route('customers.store') }}" class="space-y-4">
    @csrf
    <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80 p-6">
        <h2 class="text-lg font-semibold mb-4">Data Customer</h2>
        
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
                <label class="block text-sm font-medium mb-1">NPWP</label>
                <input type="text" name="npwp" value="{{ old('npwp') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2">
                @error('npwp')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Termin Pembayaran</label>
                <select name="payment_term" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2">
                    <option value="">Pilih Termin</option>
                    <option value="cod" @selected(old('payment_term')=='cod')>COD (Cash On Delivery)</option>
                    <option value="net_7" @selected(old('payment_term')=='net_7')>Net 7</option>
                    <option value="net_14" @selected(old('payment_term')=='net_14')>Net 14</option>
                    <option value="net_30" @selected(old('payment_term')=='net_30')>Net 30</option>
                    <option value="net_45" @selected(old('payment_term')=='net_45')>Net 45</option>
                    <option value="net_60" @selected(old('payment_term')=='net_60')>Net 60</option>
                </select>
                @error('payment_term')
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
        <a href="{{ route('customers.index') }}" class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Simpan</button>
    </div>
</form>
@endsection

