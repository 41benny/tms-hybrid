@extends('layouts.app', ['title' => 'Edit Truck'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Breadcrumb / Back Button --}}
    <div class="flex items-center gap-3">
        <x-button :href="route('trucks.index')" variant="ghost" size="sm">
            ← Kembali
        </x-button>
        <div class="text-sm text-slate-500 dark:text-slate-400">
            <span>Trucks</span> / <span class="text-slate-900 dark:text-slate-100">Edit {{ $truck->plate_number }}</span>
        </div>
    </div>

    <form method="post" action="{{ route('trucks.update', $truck) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        {{-- Form Card --}}
        <x-card title="Data Kendaraan" subtitle="Update informasi kendaraan">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="plate_number" 
                    label="Nomor Polisi" 
                    :value="old('plate_number', $truck->plate_number)"
                    :error="$errors->first('plate_number')"
                    :required="true"
                    placeholder="Contoh: B 1234 XYZ"
                />
                
                <x-select 
                    name="vehicle_type" 
                    label="Tipe Kendaraan"
                    :error="$errors->first('vehicle_type')"
                    :required="true"
                >
                    <option value="">Pilih tipe kendaraan</option>
                    <option value="Tronton" @selected(old('vehicle_type', $truck->vehicle_type)=='Tronton')>Tronton</option>
                    <option value="Fuso" @selected(old('vehicle_type', $truck->vehicle_type)=='Fuso')>Fuso</option>
                    <option value="CDD" @selected(old('vehicle_type', $truck->vehicle_type)=='CDD')>CDD</option>
                    <option value="CDE" @selected(old('vehicle_type', $truck->vehicle_type)=='CDE')>CDE</option>
                    <option value="Trailer" @selected(old('vehicle_type', $truck->vehicle_type)=='Trailer')>Trailer</option>
                    <option value="Container" @selected(old('vehicle_type', $truck->vehicle_type)=='Container')>Container</option>
                    <option value="Pickup" @selected(old('vehicle_type', $truck->vehicle_type)=='Pickup')>Pickup</option>
                    <option value="Engkel" @selected(old('vehicle_type', $truck->vehicle_type)=='Engkel')>Engkel</option>
                </x-select>
                
                <x-input 
                    name="capacity_tonase" 
                    label="Kapasitas (Ton)" 
                    type="number"
                    step="0.01"
                    :value="old('capacity_tonase', $truck->capacity_tonase)"
                    :error="$errors->first('capacity_tonase')"
                    :required="true"
                    placeholder="Contoh: 10.5"
                />
                
                <x-select 
                    name="vendor_id" 
                    label="Vendor"
                    :error="$errors->first('vendor_id')"
                    helpText="Kosongkan jika milik sendiri"
                >
                    <option value="">Pilih vendor (opsional)</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected(old('vendor_id', $truck->vendor_id)==$v->id)>{{ $v->name }}</option>
                    @endforeach
                </x-select>
                
                <x-select 
                    name="driver_id" 
                    label="Supir / Driver"
                    :error="$errors->first('driver_id')"
                    helpText="Assign driver default untuk truck ini"
                >
                    <option value="">Pilih driver (opsional)</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" @selected(old('driver_id', $truck->driver_id)==$d->id)>{{ $d->name }}</option>
                    @endforeach
                </x-select>
            </div>
            
            <div class="mt-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 cursor-pointer transition-colors">
                        <input 
                            type="checkbox" 
                            name="is_own_fleet" 
                            value="1" 
                            @checked(old('is_own_fleet', $truck->is_own_fleet))
                            class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                        >
                        <div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">🏢 Milik Sendiri</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Kendaraan milik perusahaan</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 cursor-pointer transition-colors">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1" 
                            @checked(old('is_active', $truck->is_active))
                            class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                        >
                        <div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">✅ Status Aktif</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Kendaraan siap digunakan</div>
                        </div>
                    </label>
                </div>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('trucks.index')" variant="outline">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    💾 Update Kendaraan
                </x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

