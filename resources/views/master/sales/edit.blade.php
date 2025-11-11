@extends('layouts.app', ['title' => 'Edit Sales'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Breadcrumb / Back Button --}}
    <div class="flex items-center gap-3">
        <x-button :href="route('sales.index')" variant="ghost" size="sm">
            ← Kembali
        </x-button>
        <div class="text-sm text-slate-500 dark:text-slate-400">
            <span>Sales</span> / <span class="text-slate-900 dark:text-slate-100">Edit</span>
        </div>
    </div>

    <form method="post" action="{{ route('sales.update', $sale) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        {{-- Form Card --}}
        <x-card title="Data Sales" subtitle="Update informasi sales">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="name" 
                    label="Nama Lengkap" 
                    :value="old('name', $sale->name)"
                    :error="$errors->first('name')"
                    :required="true"
                    placeholder="Masukkan nama lengkap"
                />
                
                <x-input 
                    name="phone" 
                    label="Nomor Telepon" 
                    type="tel"
                    :value="old('phone', $sale->phone)"
                    :error="$errors->first('phone')"
                    placeholder="Contoh: 0812-3456-7890"
                />
                
                <x-input 
                    name="email" 
                    label="Email" 
                    type="email"
                    :value="old('email', $sale->email)"
                    :error="$errors->first('email')"
                    placeholder="email@example.com"
                    class="md:col-span-2"
                />
                
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 cursor-pointer transition-colors">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1" 
                            @checked(old('is_active', $sale->is_active))
                            class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                        >
                        <div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">✅ Status Aktif</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Sales dapat melakukan penjualan</div>
                        </div>
                    </label>
                </div>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('sales.index')" variant="outline">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    💾 Update Sales
                </x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

