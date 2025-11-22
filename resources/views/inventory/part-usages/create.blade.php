@extends('layouts.app', ['title' => 'Tambah Pemakaian Part'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Pemakaian Part</div>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Catat pemakaian sparepart</p>
                </div>
                <x-button :href="route('part-usages.index')" variant="ghost" size="sm">Batal</x-button>
            </div>
        </x-slot:header>

        <form method="POST" action="{{ route('part-usages.store') }}" class="space-y-4 md:space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Pemakaian <span class="text-red-500">*</span></label>
                    <input type="date" name="usage_date" value="{{ old('usage_date', date('Y-m-d')) }}" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                    @error('usage_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Part <span class="text-red-500">*</span></label>
                    <select name="part_id" id="part_id" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                        <option value="">Pilih Part</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}" data-stock="{{ $part->stocks->sum('quantity') }}" data-unit="{{ $part->unit }}">
                                {{ $part->code }} - {{ $part->name }} (Stok: {{ number_format($part->stocks->sum('quantity'), 2) }} {{ $part->unit }})
                            </option>
                        @endforeach
                    </select>
                    @error('part_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p id="stock-info" class="mt-1 text-xs text-slate-500 dark:text-slate-400"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" required min="0.01" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                    @error('quantity')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Truck (Opsional)</label>
                    <select name="truck_id" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                        <option value="">Pilih Truck</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}" @selected(old('truck_id') == $truck->id)>{{ $truck->plate_number }}</option>
                        @endforeach
                    </select>
                    @error('truck_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jenis Pemakaian <span class="text-red-500">*</span></label>
                    <select name="usage_type" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                        <option value="maintenance" @selected(old('usage_type') === 'maintenance')>Maintenance</option>
                        <option value="repair" @selected(old('usage_type') === 'repair')>Repair</option>
                        <option value="replacement" @selected(old('usage_type') === 'replacement')>Replacement</option>
                        <option value="other" @selected(old('usage_type') === 'other')>Lainnya</option>
                    </select>
                    @error('usage_type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2d2d2d]">
                <x-button :href="route('part-usages.index')" variant="outline" class="w-full sm:w-auto">Batal</x-button>
                <x-button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const partSelect = document.getElementById('part_id');
    const stockInfo = document.getElementById('stock-info');

    partSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const stock = parseFloat(selectedOption.dataset.stock) || 0;
            const unit = selectedOption.dataset.unit || '';
            stockInfo.textContent = `Stok tersedia: ${stock.toLocaleString('id-ID', {minimumFractionDigits: 2})} ${unit}`;
            stockInfo.className = stock > 0 ? 'mt-1 text-xs text-emerald-600' : 'mt-1 text-xs text-red-600';
        } else {
            stockInfo.textContent = '';
        }
    });
});
</script>
@endsection

