@extends('layouts.app', ['title' => 'Tambah Truck'])

@section('content')
<form method="post" action="{{ route('trucks.store') }}" class="space-y-4">
    @csrf
    <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80 p-6">
        <h2 class="text-lg font-semibold mb-4">Data Truck</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">No. Polisi <span class="text-red-500">*</span></label>
                <input type="text" name="plate_number" value="{{ old('plate_number') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2" required>
                @error('plate_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Tipe Kendaraan <span class="text-red-500">*</span></label>
                <select name="vehicle_type" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2" required>
                    <option value="">Pilih Tipe</option>
                    <option value="Tronton" @selected(old('vehicle_type')=='Tronton')>Tronton</option>
                    <option value="Fuso" @selected(old('vehicle_type')=='Fuso')>Fuso</option>
                    <option value="CDD" @selected(old('vehicle_type')=='CDD')>CDD</option>
                    <option value="CDE" @selected(old('vehicle_type')=='CDE')>CDE</option>
                    <option value="Trailer" @selected(old('vehicle_type')=='Trailer')>Trailer</option>
                    <option value="Container" @selected(old('vehicle_type')=='Container')>Container</option>
                    <option value="Pickup" @selected(old('vehicle_type')=='Pickup')>Pickup</option>
                    <option value="Engkel" @selected(old('vehicle_type')=='Engkel')>Engkel</option>
                </select>
                @error('vehicle_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Kapasitas (Ton) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="capacity_tonase" value="{{ old('capacity_tonase') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2" required>
                @error('capacity_tonase')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Vendor (Jika Bukan Milik Sendiri)</label>
                <select name="vendor_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2">
                    <option value="">Pilih Vendor</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected(old('vendor_id')==$v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
                @error('vendor_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="md:col-span-2">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_own_fleet" value="1" id="is_own_fleet" class="rounded border-slate-300 dark:border-slate-700" @checked(old('is_own_fleet', true))>
                        <label for="is_own_fleet" class="text-sm">Milik Sendiri</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" id="is_active" class="rounded border-slate-300 dark:border-slate-700" @checked(old('is_active', true))>
                        <label for="is_active" class="text-sm">Aktif</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('trucks.index') }}" class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Simpan</button>
    </div>
</form>
@endsection

