{{-- Step 2: Informasi Invoice --}}
<x-card title="2. Informasi Invoice" collapsible="true">
    @if(!$selectedCustomer)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-600/40 dark:bg-amber-900/20 px-4 py-3 text-sm text-amber-700 dark:text-amber-200">
            Silakan pilih customer terlebih dahulu agar data alamat, JO, dan perhitungan invoice bisa otomatis terisi.
        </div>
    @endif

    <input type="hidden" name="reference" id="reference_hidden" value="{{ old('reference', request('reference')) }}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Tipe Transaksi
            </label>
            <select name="transaction_type" 
                    id="transaction_type_select"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    onchange="if(typeof window.recalcPpn === 'function') window.recalcPpn();">
                <option value="04" {{ old('transaction_type', '04') == '04' ? 'selected' : '' }}>04 - DPP Nilai Lain (11%)</option>
                <option value="05" {{ old('transaction_type') == '05' ? 'selected' : '' }}>05 - Besaran Tertentu (1.1%)</option>
                <option value="08" {{ old('transaction_type') == '08' ? 'selected' : '' }}>08 - Dibebaskan (0%)</option>
                <option value="01" {{ old('transaction_type') == '01' ? 'selected' : '' }}>01 - Kepada Pihak Lain Bukan Pemungut PPN (11%)</option>
                <option value="02" {{ old('transaction_type') == '02' ? 'selected' : '' }}>02 - Kepada Pemungut Bendaharawan (11%)</option>
                <option value="03" {{ old('transaction_type') == '03' ? 'selected' : '' }}>03 - Kepada Pemungut Selain Bendaharawan (11%)</option>
                <option value="06" {{ old('transaction_type') == '06' ? 'selected' : '' }}>06 - Penyerahan Lainnya (11%)</option>
                <option value="07" {{ old('transaction_type') == '07' ? 'selected' : '' }}>07 - Tidak Dipungut (0%)</option>
                <option value="09" {{ old('transaction_type') == '09' ? 'selected' : '' }}>09 - Aktiva Pasal 16D (11%)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Nomor Invoice
            </label>
            <input type="text"
                   name="invoice_number"
                   value="{{ old('invoice_number', $nextInvoiceNumber ?? 'Akan digenerate otomatis') }}"
                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                Kosongkan untuk menggunakan nomor otomatis.
            </p>
        </div>
        <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Tanggal Invoice *
        </label>
        <input type="date"
               name="invoice_date"
               value="{{ $invoiceDate }}"
               class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm"
               required>
        @error('invoice_date')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
        <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Jatuh Tempo *
        </label>
        <input type="date"
               name="due_date"
               value="{{ $dueDate }}"
               class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm"
               required>
        @error('due_date')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Term (Hari)
            </label>
            <input type="number"
                   name="payment_terms"
                   id="payment_terms"
                   min="0"
                   step="1"
                   value="{{ $paymentTerms }}"
                   placeholder="30"
                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
            @error('payment_terms')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Catatan
        </label>
        <textarea name="notes"
                  rows="3"
                  class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">{{ $notes }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</x-card>
