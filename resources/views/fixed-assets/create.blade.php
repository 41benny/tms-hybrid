@extends('layouts.app', ['title' => 'Tambah Aset Tetap'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Breadcrumb / Back Button --}}
    <div class="flex items-center gap-3">
        <x-button :href="route('fixed-assets.index')" variant="ghost" size="sm">
            ← Kembali
        </x-button>
        <div class="text-sm text-slate-500 dark:text-slate-400">
            <span>Aset Tetap</span> / <span class="text-slate-900 dark:text-slate-100">Tambah Baru</span>
        </div>
    </div>

    <form method="post" action="{{ route('fixed-assets.store') }}" class="space-y-6">
        @csrf
        
        {{-- Informasi Dasar --}}
        <x-card title="Informasi Dasar" subtitle="Data identitas aset tetap">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="code" 
                    label="Kode Aset" 
                    :value="old('code')"
                    :error="$errors->first('code')"
                    :required="true"
                    placeholder="Contoh: AST-001"
                />
                
                <x-input 
                    name="name" 
                    label="Nama Aset" 
                    :value="old('name')"
                    :error="$errors->first('name')"
                    :required="true"
                    placeholder="Contoh: Komputer Dell Latitude"
                />
            </div>
        </x-card>

        {{-- Nilai & Depresiasi --}}
        <x-card title="Nilai & Depresiasi" subtitle="Informasi perolehan dan umur ekonomis">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="acquisition_date" 
                    label="Tanggal Perolehan" 
                    type="date"
                    :value="old('acquisition_date')"
                    :error="$errors->first('acquisition_date')"
                    :required="true"
                />
                
                <x-input 
                    name="acquisition_cost" 
                    label="Nilai Perolehan" 
                    type="number"
                    step="0.01"
                    :value="old('acquisition_cost')"
                    :error="$errors->first('acquisition_cost')"
                    :required="true"
                    placeholder="Contoh: 15000000"
                />
                
                <x-input 
                    name="useful_life_months" 
                    label="Umur Ekonomis (bulan)" 
                    type="number"
                    :value="old('useful_life_months')"
                    :error="$errors->first('useful_life_months')"
                    :required="true"
                    placeholder="Contoh: 60"
                    helpText="Estimasi umur manfaat aset dalam bulan"
                />
                
                <x-input 
                    name="residual_value" 
                    label="Nilai Residu" 
                    type="number"
                    step="0.01"
                    :value="old('residual_value', 0)"
                    :error="$errors->first('residual_value')"
                    placeholder="Contoh: 1000000"
                    helpText="Nilai sisa di akhir masa manfaat"
                />
            </div>
        </x-card>

        {{-- Akun Akuntansi --}}
        <x-card title="Akun Akuntansi" subtitle="Mapping ke chart of accounts">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-select 
                    name="account_asset_id" 
                    label="Akun Aset"
                    :error="$errors->first('account_asset_id')"
                    :required="true"
                >
                    <option value="">Pilih Akun Aset</option>
                    @foreach($assetAccounts as $a)
                        <option value="{{ $a->id }}" @selected(old('account_asset_id')==$a->id)>
                            {{ $a->code }} - {{ $a->name }}
                        </option>
                    @endforeach
                </x-select>
                
                <x-select 
                    name="account_accum_id" 
                    label="Akun Akumulasi Penyusutan"
                    :error="$errors->first('account_accum_id')"
                    :required="true"
                >
                    <option value="">Pilih Akun Akumulasi</option>
                    @foreach($accumAccounts as $a)
                        <option value="{{ $a->id }}" @selected(old('account_accum_id')==$a->id)>
                            {{ $a->code }} - {{ $a->name }}
                        </option>
                    @endforeach
                </x-select>
                
                <x-select 
                    name="account_expense_id" 
                    label="Akun Beban Penyusutan"
                    :error="$errors->first('account_expense_id')"
                    :required="true"
                >
                    <option value="">Pilih Akun Beban</option>
                    @foreach($expenseAccounts as $a)
                        <option value="{{ $a->id }}" @selected(old('account_expense_id')==$a->id)>
                            {{ $a->code }} - {{ $a->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('fixed-assets.index')" variant="outline">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    💾 Simpan Aset Tetap
                </x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
