@extends('layouts.app', ['title' => 'Tambah Pembelian Part'])

@section('content')
<div class="space-y-4 md:space-y-6" x-data="purchaseForm()">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Pembelian Part</h1>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Input pembelian sparepart baru</p>
                </div>
                <x-button :href="route('part-purchases.index')" variant="ghost" size="sm">Batal</x-button>
            </div>
        </x-slot:header>

        <form method="POST" action="{{ route('part-purchases.store') }}" @submit.prevent="submitForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Pembelian <span class="text-red-500">*</span></label>
                    <input type="date" name="purchase_date" x-model="formData.purchase_date" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor <span class="text-red-500">*</span></label>
                    <select name="vendor_id" x-model="formData.vendor_id" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                        <option value="">Pilih Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">No Invoice Supplier</label>
                    <input type="text" name="invoice_number" x-model="formData.invoice_number" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_direct_usage" x-model="formData.is_direct_usage" class="rounded border-slate-300 text-indigo-600">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Langsung Pakai (tidak masuk stok)</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Catatan</label>
                    <textarea name="notes" x-model="formData.notes" rows="2" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm"></textarea>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-[#2d2d2d] pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Item Pembelian</h3>
                    <button type="button" @click="addItem" class="px-3 py-2 rounded bg-indigo-600 text-white text-sm hover:bg-indigo-500">+ Tambah Item</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in formData.items" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 p-3 border border-slate-200 dark:border-[#2d2d2d] rounded-lg">
                            <div class="md:col-span-4">
                                <select :name="`items[${index}][part_id]`" x-model="item.part_id" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm">
                                    <option value="">Pilih Part</option>
                                    @foreach($parts as $part)
                                        <option value="{{ $part->id }}">{{ $part->code }} - {{ $part->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <input type="number" step="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calculateSubtotal(index)" placeholder="Qty" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" @input="calculateSubtotal(index)" placeholder="Harga" required class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-3">
                                <input type="text" :name="`items[${index}][notes]`" x-model="item.notes" placeholder="Catatan" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-1 flex items-center justify-end">
                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800" :disabled="formData.items.length <= 1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 p-4 bg-slate-50 dark:bg-[#252525] rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-slate-900 dark:text-slate-100">Total:</span>
                        <span class="text-xl font-bold text-indigo-600" x-text="formatNumber(totalAmount)"></span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200 dark:border-[#2d2d2d] mt-6">
                <x-button :href="route('part-purchases.index')" variant="outline" class="w-full sm:w-auto">Batal</x-button>
                <x-button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
function purchaseForm() {
    return {
        formData: {
            purchase_date: new Date().toISOString().split('T')[0],
            vendor_id: '',
            invoice_number: '',
            is_direct_usage: false,
            notes: '',
            items: [
                { part_id: '', quantity: 0, unit_price: 0, notes: '' }
            ]
        },
        
        get totalAmount() {
            return this.formData.items.reduce((sum, item) => {
                return sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0));
            }, 0);
        },
        
        addItem() {
            this.formData.items.push({ part_id: '', quantity: 0, unit_price: 0, notes: '' });
        },
        
        removeItem(index) {
            if (this.formData.items.length > 1) {
                this.formData.items.splice(index, 1);
            }
        },
        
        calculateSubtotal(index) {
            // Auto calculate bisa ditambahkan jika perlu
        },
        
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
        },
        
        submitForm() {
            this.$el.submit();
        }
    }
}
</script>
@endsection

