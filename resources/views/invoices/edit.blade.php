@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Edit Invoice {{ $invoice->invoice_number }}
        </h1>
        <a href="{{ route('invoices.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
            &larr; Kembali ke List
        </a>
    </div>

    <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm" onsubmit="submitInvoiceFormWithScroll(this)">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" value="{{ $invoice->status }}">

        <x-card class="p-6">
            {{-- Header Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Kiri: Customer Info --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        {{-- Customer Selection --}}
                        <div class="relative">
                            <input type="text"
                                   id="customer_search"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Ketik nama customer..."
                                   autocomplete="off"
                                   value="{{ $invoice->customer->name ?? '' }}">
                            <input type="hidden" name="customer_id" id="customer_id_input" value="{{ $invoice->customer_id }}">

                            {{-- Suggestions Dropdown --}}
                            <div id="customer_suggestions" class="absolute z-10 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                {{-- Populated by JS --}}
                            </div>
                        </div>
                        @error('customer_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Alamat
                        </label>
                        <textarea name="customer_address" rows="3" readonly
                                  class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-500 dark:text-slate-400"
                        >{{ $invoice->customer->address ?? '' }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                No. Telp
                            </label>
                            <input type="text" name="customer_phone" readonly
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-500 dark:text-slate-400"
                                   value="{{ $invoice->customer->phone ?? '' }}">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                NPWP
                            </label>
                            <input type="text" name="customer_npwp" readonly
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-500 dark:text-slate-400"
                                   value="{{ $invoice->customer->npwp ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- Kanan: Invoice Details --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                No. Invoice
                            </label>
                            <input type="text" name="invoice_number"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-300"
                                   value="{{ old('invoice_number', $invoice->invoice_number) }}"
                                   placeholder="Auto-generated if empty">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                Tanggal Invoice <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="invoice_date"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                Payment Terms (Hari)
                            </label>
                            <input type="number" id="payment_terms" name="payment_terms"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   value="{{ old('payment_terms', $invoice->payment_terms) }}" placeholder="e.g. 30">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                Jatuh Tempo <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="due_date"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Referensi / PO Number
                        </label>
                        <input type="text" id="reference_header" name="reference"
                               class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                               value="{{ old('reference', $invoice->reference) }}">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Tipe Transaksi (PPN)
                        </label>
                        @php
                            // Coba tebak tax code dari tax amount
                            $currentTaxCode = old('tax_code');
                            if (!$currentTaxCode && $invoice->tax_amount > 0 && $invoice->subtotal > 0) {
                                $ratio = $invoice->tax_amount / $invoice->subtotal;
                                if (abs($ratio - 0.11) < 0.001) $currentTaxCode = '04';
                                elseif (abs($ratio - 0.011) < 0.001) $currentTaxCode = '05';
                            } elseif (!$currentTaxCode && $invoice->tax_amount == 0) {
                                $currentTaxCode = '08';
                            }
                        @endphp
                        <select id="tax_code_select_header" name="tax_code" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">-- Pilih Tipe PPN --</option>
                            <option value="04" @selected($currentTaxCode == '04')>04 - DPP Nilai Lain (11%)</option>
                            <option value="05" @selected($currentTaxCode == '05')>05 - Besaran Tertentu (1.1%)</option>
                            <option value="08" @selected($currentTaxCode == '08')>08 - Dibebaskan (0%)</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Pilih tipe PPN untuk hitung otomatis.</p>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                        Catatan (Tampil di Invoice)
                    </label>
                    <textarea name="notes" rows="2"
                              class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >{{ old('notes', $invoice->notes) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                        Catatan Internal (Tidak Tampil)
                    </label>
                    <textarea name="internal_notes" rows="2"
                              class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >{{ old('internal_notes', $invoice->internal_notes) }}</textarea>
                </div>
            </div>

            {{-- Items Section --}}
            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                        Item Invoice
                    </h3>

                    {{-- Tombol Pilih Job Order --}}
                    @if($invoice->customer_id)
                        <button type="button"
                                onclick="openJobOrderModal()"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah dari Job Order
                        </button>
                    @else
                        <span class="text-sm text-slate-500 italic">Pilih customer dulu untuk ambil Job Order</span>
                    @endif
                </div>

                @if(isset($previewItems) && count($previewItems) > 0)
                    <div class="space-y-4" id="invoice-items-container" data-next-index="{{ count($previewItems) }}">
                        @php
                            $subtotalPreview = 0;
                            $lastItemExcludeTax = null;
                        @endphp
                        @foreach($previewItems as $index => $item)
                            @php
                                $qty = (float) $item['quantity'];
                                $price = (float) $item['unit_price'];
                                $amount = $qty * $price;
                                $subtotalPreview += $amount;

                                // Detect billable items: items with exclude_tax = true and has shipment_leg_id (insurance billable)
                                $currentExcludeTax = !empty($item['exclude_tax']);
                                $isFirstBillableItem = ($lastItemExcludeTax === false || $lastItemExcludeTax === null) && $currentExcludeTax === true;
                                $lastItemExcludeTax = $currentExcludeTax;
                            @endphp

                            {{-- Separator between main items and billable items --}}
                            @if($isFirstBillableItem)
                                <div class="flex items-center gap-3 py-3">
                                    <div class="flex-1 border-t-2 border-dashed border-amber-300 dark:border-amber-700"></div>
                                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider px-3 py-1 bg-amber-50 dark:bg-amber-900/20 rounded-full">
                                        📋 Biaya Tambahan (Billable)
                                    </span>
                                    <div class="flex-1 border-t-2 border-dashed border-amber-300 dark:border-amber-700"></div>
                                </div>
                            @endif

                            <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30">
                                {{-- Hidden Fields --}}
                                <input type="hidden" name="items[{{ $index }}][job_order_id]" value="{{ $item['job_order_id'] ?? '' }}">
                                <input type="hidden" name="items[{{ $index }}][shipment_leg_id]" value="{{ $item['shipment_leg_id'] ?? '' }}">
                                <input type="hidden" name="items[{{ $index }}][item_type]" value="{{ $item['item_type'] ?? 'other' }}">

                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Deskripsi</label>
                                        <input type="text" name="items[{{ $index }}][description]"
                                               value="{{ $item['description'] }}"
                                               class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Qty</label>
                                        <input type="number" step="0.01" min="0.01"
                                               name="items[{{ $index }}][quantity]"
                                               value="{{ $qty }}"
                                               class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Unit Price</label>
                                        <input type="number" step="0.01" min="0"
                                               name="items[{{ $index }}][unit_price]"
                                               value="{{ $price }}"
                                               class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Subtotal</label>
                                        <div class="w-full rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100 item-subtotal">
                                            Rp {{ number_format($amount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Checkbox untuk Exclude Tax --}}
                                <div class="mt-3 flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="items[{{ $index }}][exclude_tax]" 
                                           id="exclude_tax_{{ $index }}"
                                           value="1"
                                           {{ !empty($item['exclude_tax']) ? 'checked' : '' }}
                                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="exclude_tax_{{ $index }}" class="text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                                        <span class="font-medium">Exclude dari PPN</span>
                                        <span class="text-slate-500 dark:text-slate-500"> (Item ini tidak dikenakan pajak)</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <button type="button" onclick="addInvoiceItemRow()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Baris Manual
                        </button>
                        <p class="text-xs text-slate-500 mt-1">
                            Gunakan tombol ini untuk menambah baris transaksi tambahan (misalnya memecah satu JO menjadi beberapa deskripsi/tagihan).
                        </p>
                    </div>

                    {{-- Tax & Discount --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-2">
                                Tax Amount (PPN)
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="tax_amount"
                                   id="tax_amount_input"
                                   value="{{ old('tax_amount', $invoice->tax_amount) }}"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <p class="text-xs text-slate-500 mt-1">Otomatis terisi jika Tipe Transaksi dipilih.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-2">
                                PPh 23 Amount
                            </label>
                            <div class="flex gap-2">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="pph23_amount"
                                       id="pph23_amount_input"
                                       value="{{ old('pph23_amount', $invoice->pph23_amount) }}"
                                       class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <button type="button" id="btn_calc_pph23" class="px-3 py-2 bg-slate-200 dark:bg-slate-700 rounded text-xs font-medium hover:bg-slate-300 dark:hover:bg-slate-600">
                                    Hitung 2%
                                </button>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="checkbox"
                                       name="show_pph23"
                                       id="show_pph23_checkbox"
                                       value="1"
                                       {{ old('show_pph23', $invoice->show_pph23) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="show_pph23_checkbox" class="text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                                    Tampilkan PPh 23 di Invoice
                                </label>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Potongan PPh 23 (jika ada).</p>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-2">
                                Discount Amount
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="discount_amount"
                                   id="discount_amount_input"
                                   value="{{ old('discount_amount', $invoice->discount_amount) }}"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>

                    <input type="hidden" id="invoice_subtotal_value" value="{{ $subtotalPreview }}">

                    <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 space-y-2">
                        {{-- Breakdown: Taxable vs Non-Taxable --}}
                        <div class="pb-2 mb-2 border-b border-slate-200 dark:border-slate-700">
                            <div class="flex justify-between items-center text-xs text-slate-600 dark:text-slate-400">
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Subtotal Kena PPN
                                </span>
                                <span id="display_taxable_subtotal" class="font-medium text-blue-600 dark:text-blue-400">Rp 0</span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-slate-600 dark:text-slate-400 mt-1">
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    Subtotal Tidak Kena PPN
                                </span>
                                <span id="display_nontaxable_subtotal" class="font-medium text-amber-600 dark:text-amber-400">Rp 0</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Subtotal</span>
                            <span class="font-medium text-slate-900 dark:text-slate-100" id="display_subtotal">
                                Rp {{ number_format($subtotalPreview, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- DPP Nilai Lain Option --}}
                        <div class="flex justify-between items-center text-sm py-1">
                            <div class="flex items-center gap-2">
                                <input type="checkbox"
                                       id="use_dpp_nilai_lain"
                                       name="use_dpp_nilai_lain"
                                       value="1"
                                       @checked($invoice->transaction_type == '04')
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="use_dpp_nilai_lain" class="text-slate-600 dark:text-slate-400 cursor-pointer select-none">
                                    Gunakan DPP Nilai Lain (11/12)
                                </label>
                            </div>
                        </div>

                        {{-- DPP Nilai Lain Display --}}
                        <div id="dpp_nilai_lain_row" class="flex justify-between items-center text-sm hidden">
                            <span class="text-slate-600 dark:text-slate-400 pl-6">DPP Nilai Lain</span>
                            <span class="text-slate-900 dark:text-slate-100 font-mono" id="display_dpp_nilai_lain">
                                Rp 0
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 dark:text-slate-400">PPN</span>
                            <span class="font-medium text-slate-900 dark:text-slate-100" id="display_tax">
                                Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Discount</span>
                            <span class="font-medium text-slate-900 dark:text-slate-100" id="display_discount">
                                - Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-base font-bold border-t border-slate-100 dark:border-slate-800 pt-2">
                            <span class="text-slate-800 dark:text-slate-200">Total Tagihan (Receivable)</span>
                            <span class="text-indigo-600 dark:text-indigo-400" id="display_total">
                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-amber-600 dark:text-amber-400 pt-1">
                            <span>Potongan PPh 23 (Estimasi)</span>
                            <span id="display_pph23">- Rp {{ number_format($invoice->pph23_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-semibold text-emerald-600 dark:text-emerald-400 pt-1">
                            <span>Sisa Tagihan (Net Payable)</span>
                            <span id="display_net_payable">Rp {{ number_format($invoice->total_amount - $invoice->pph23_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    {{-- Submit --}}
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <x-button :href="route('invoices.index')" variant="outline">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan
                        </x-button>
                    </div>
                @else
                    {{-- Empty state --}}
                    <div class="text-center py-12">
                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                            Tidak ada item. Silakan tambah dari Job Order atau manual.
                        </p>
                        <div class="mt-4">
                            <button type="button" onclick="addInvoiceItemRow()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Tambah Baris Manual
                            </button>
                        </div>
                    </div>
                @endif
        </x-card>
    </form>

    {{-- Modal: Pilih Job Order --}}
    <x-modal id="jobOrderModal" title="Pilih Job Order">
        <div class="space-y-4" id="jobOrderFormContainer">
            <input type="hidden" id="modal_customer_id" value="{{ $invoice->customer_id }}">
            <input type="hidden" id="modal_invoice_id" value="{{ $invoice->id }}">
            
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    Job Order untuk {{ $invoice->customer->name }}
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Filter Status:</span>
                    <select name="status_filter" id="status_filter_edit"
                            class="rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1 text-xs"
                            onchange="updateJobOrderListEdit()">
                        <option value="completed" @selected(request('status_filter') === 'completed')>Hanya Completed</option>
                        <option value="all" @selected(request('status_filter') === 'all')>Semua Status</option>
                    </select>
                </div>
            </div>

            <div id="job-order-list-container-edit" class="border border-slate-200 dark:border-slate-700 rounded-lg max-h-80 overflow-y-auto divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($jobOrders as $jo)
                    @php
                        $mainItem  = $jo->items->first();
                        $equipment = $mainItem?->equipment?->name ?? $mainItem?->cargo_type;
                        $qty       = $mainItem?->quantity;
                        $qtyText   = $qty !== null ? ((float) $qty + 0).' unit' : null;
                        $firstLeg  = $jo->shipmentLegs->sortBy('load_date')->first();

                        // Cek apakah JO ini sudah ada di previewItems (berarti sudah dipilih/ada di invoice)
                        $isAlreadySelected = false;
                        if(isset($previewItems)) {
                            foreach($previewItems as $pi) {
                                if(($pi['job_order_id'] ?? null) == $jo->id) {
                                    $isAlreadySelected = true;
                                    break;
                                }
                            }
                        }
                    @endphp

                    <label class="flex items-start gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                        <input type="checkbox"
                               name="job_order_ids[]"
                               value="{{ $jo->id }}"
                               class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                               @checked($isAlreadySelected)>

                        <div class="text-sm">
                            <div class="font-semibold text-slate-900 dark:text-slate-100">
                                {{ $jo->job_number }}
                            </div>

                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $jo->origin }} → {{ $jo->destination }}
                            </div>

                            @if($equipment || $qtyText || ($firstLeg && $firstLeg->load_date))
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    @if($equipment) <span>{{ $equipment }}</span> @endif
                                    @if($qtyText)
                                        @if($equipment) <span class="mx-1">•</span> @endif
                                        <span>{{ $qtyText }}</span>
                                    @endif
                                    @if($firstLeg && $firstLeg->load_date)
                                        @if($equipment || $qtyText) <span class="mx-1">•</span> @endif
                                        <span>Load: {{ $firstLeg->load_date->format('d M Y') }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                Status: {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                                • Nilai Tagihan: Rp {{ number_format((float) ($jo->invoice_amount ?? 0), 0, ',', '.') }}
                            </div>
                        </div>
                    </label>
                @empty
                    <div class="p-4 text-sm text-slate-500 dark:text-slate-400">
                        Tidak ada Job Order tambahan yang tersedia.
                    </div>
                @endforelse
            </div>

            <div class="flex justify-between items-center mt-4">
                <button type="button"
                        onclick="closeJobOrderModal()"
                        class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                    Batal
                </button>
                <x-button type="button" variant="primary" onclick="addSelectedJobOrdersEdit()">
                    Update Item List
                </x-button>
            </div>
        </div>

        <script>
            function updateJobOrderListEdit() {
                const status = document.getElementById('status_filter_edit').value;
                const customerId = document.getElementById('modal_customer_id').value;
                const invoiceId = document.getElementById('modal_invoice_id').value;
                const container = document.getElementById('job-order-list-container-edit');
                
                // Show loading state
                container.innerHTML = '<div class="p-4 text-center text-sm text-slate-500">Memuat...</div>';
                
                // Fetch updated list via AJAX
                fetch(`/invoices/${invoiceId}/edit?load_job_orders=1&customer_id=${customerId}&status_filter=${status}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="p-4 text-center text-sm text-red-500">Gagal memuat data.</div>';
                });
            }

            function addSelectedJobOrdersEdit() {
                const checkboxes = document.querySelectorAll('#job-order-list-container-edit input[type="checkbox"]:checked');
                const selectedIds = Array.from(checkboxes).map(cb => cb.value);
                
                if (selectedIds.length === 0) {
                    alert('Pilih minimal satu Job Order');
                    return;
                }

                const customerId = document.getElementById('modal_customer_id').value;
                
                // Show loading state
                const container = document.getElementById('invoice-items-container');
                if (!container) {
                    console.error('Invoice items container not found');
                    return;
                }

                // Fetch job order details and add to invoice
                const params = new URLSearchParams({
                    customer_id: customerId,
                    job_order_ids: selectedIds.join(','),
                    is_dp: '0',
                    fetch_items: '1'
                });

                fetch(`{{ route('invoices.create') }}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        // Add items to the invoice
                        data.items.forEach(item => {
                            addJobOrderItemToInvoiceEdit(item);
                        });
                        
                        // Recalculate totals
                        if (typeof window.recalcPpn === 'function') {
                            window.recalcPpn();
                        }
                        
                        // Close modal
                        closeJobOrderModal();
                        
                        // Show success message
                        console.log(`✅ Added ${data.items.length} job order(s) to invoice`);
                    } else {
                        alert('Tidak ada item yang bisa ditambahkan');
                    }
                })
                .catch(error => {
                    console.error('Error adding job orders:', error);
                    alert('Gagal menambahkan Job Order. Silakan coba lagi.');
                });
            }

            function addJobOrderItemToInvoiceEdit(item) {
                const container = document.getElementById('invoice-items-container');
                if (!container) return;

                let nextIndex = parseInt(container.getAttribute('data-next-index') || '0', 10);
                if (isNaN(nextIndex) || nextIndex < 0) {
                    nextIndex = 0;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30 relative group';

                const amount = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);

                wrapper.innerHTML =
                    '<button type="button" onclick="removeInvoiceItemRow(this)" class="absolute top-2 right-2 p-1.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus item ini">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
                    '</svg>' +
                    '</button>' +
                    '<input type="hidden" name="items[' + nextIndex + '][job_order_id]" value="' + (item.job_order_id || '') + '">' +
                    '<input type="hidden" name="items[' + nextIndex + '][shipment_leg_id]" value="' + (item.shipment_leg_id || '') + '">' +
                    '<input type="hidden" name="items[' + nextIndex + '][item_type]" value="' + (item.item_type || 'job_order') + '">' +
                    '<div class="grid grid-cols-1 md:grid-cols-5 gap-3">' +
                    '<div class="md:col-span-2">' +
                    '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Deskripsi</label>' +
                    '<input type="text" name="items[' + nextIndex + '][description]" value="' + (item.description || '') + '" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
                    '</div>' +
                    '<div>' +
                    '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Qty</label>' +
                    '<input type="number" step="0.01" min="0.01" name="items[' + nextIndex + '][quantity]" value="' + (item.quantity || 1) + '" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
                    '</div>' +
                    '<div>' +
                    '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Unit Price</label>' +
                    '<input type="number" step="0.01" min="0" name="items[' + nextIndex + '][unit_price]" value="' + (item.unit_price || 0) + '" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
                    '</div>' +
                    '<div>' +
                    '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Subtotal</label>' +
                    '<div class="w-full rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100 item-subtotal">Rp ' + amount.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="mt-3 flex items-center gap-2">' +
                    '<input type="checkbox" name="items[' + nextIndex + '][exclude_tax]" id="exclude_tax_' + nextIndex + '" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">' +
                    '<label for="exclude_tax_' + nextIndex + '" class="text-xs text-slate-600 dark:text-slate-400 cursor-pointer">' +
                    '<span class="font-medium">Exclude dari PPN</span>' +
                    '<span class="text-slate-500 dark:text-slate-500"> (Item ini tidak dikenakan pajak)</span>' +
                    '</label>' +
                    '</div>';

                container.appendChild(wrapper);
                container.setAttribute('data-next-index', String(nextIndex + 1));

                // Hook subtotal auto-update for new row
                const qtyInput = wrapper.querySelector('input[name*="[quantity]"]');
                const priceInput = wrapper.querySelector('input[name*="[unit_price]"]');
                const subtotalDisplay = wrapper.querySelector('.item-subtotal');
                const excludeTaxCheckbox = wrapper.querySelector('input[name*="[exclude_tax]"]');

                if (qtyInput && priceInput && subtotalDisplay) {
                    function updateSubtotal() {
                        const qty = parseFloat(qtyInput.value) || 0;
                        const price = parseFloat(priceInput.value) || 0;
                        const subtotal = qty * price;
                        subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });

                        if (typeof window.recalcPpn === 'function') {
                            window.recalcPpn();
                        }
                    }

                    qtyInput.addEventListener('input', updateSubtotal);
                    priceInput.addEventListener('input', updateSubtotal);
                }

                if (excludeTaxCheckbox) {
                    excludeTaxCheckbox.addEventListener('change', function () {
                        if (typeof window.recalcPpn === 'function') {
                            window.recalcPpn();
                        }
                    });
                }
            }
        </script>
    </x-modal>

    @php
        $customerLookup = $customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address,
                'phone' => $c->phone,
                'npwp' => $c->npwp,
            ];
        })->values();
    @endphp

    <script>
        function addInvoiceItemRow() {
            const container = document.getElementById('invoice-items-container');
            if (!container) return;

            let nextIndex = parseInt(container.getAttribute('data-next-index') || '0', 10);
            if (isNaN(nextIndex) || nextIndex < 0) {
                nextIndex = 0;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30';

            wrapper.innerHTML =
                '<input type="hidden" name="items[' + nextIndex + '][job_order_id]" value="">' +
                '<input type="hidden" name="items[' + nextIndex + '][shipment_leg_id]" value="">' +
                '<input type="hidden" name="items[' + nextIndex + '][item_type]" value="other">' +
                '<input type="hidden" name="items[' + nextIndex + '][exclude_tax]" value="0">' +
                '<div class="grid grid-cols-1 md:grid-cols-5 gap-3">' +
                    '<div class="md:col-span-2">' +
                        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Deskripsi</label>' +
                        '<input type="text" name="items[' + nextIndex + '][description]" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Qty</label>' +
                        '<input type="number" step="0.01" min="0.01" name="items[' + nextIndex + '][quantity]" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Unit Price</label>' +
                        '<input type="number" step="0.01" min="0" name="items[' + nextIndex + '][unit_price]" class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm" required>' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Subtotal</label>' +
                        '<div class="w-full rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100 item-subtotal">Rp 0</div>' +
                    '</div>' +
                '</div>';

            container.appendChild(wrapper);
            container.setAttribute('data-next-index', String(nextIndex + 1));

            // Hook subtotal auto-update for new row
            const qtyInput = wrapper.querySelector('input[name*="[quantity]"]');
            const priceInput = wrapper.querySelector('input[name*="[unit_price]"]');
            const subtotalDisplay = wrapper.querySelector('.item-subtotal');

            if (qtyInput && priceInput && subtotalDisplay) {
                function updateSubtotal() {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    const subtotal = qty * price;
                    subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }

                qtyInput.addEventListener('input', updateSubtotal);
                priceInput.addEventListener('input', updateSubtotal);
            }
        }

        function openJobOrderModal() {
            const modal = document.getElementById('jobOrderModal');
            if (!modal) return;

            const invDate = document.querySelector('input[name="invoice_date"]');
            const dueDate = document.querySelector('input[name="due_date"]');
            const terms   = document.querySelector('input[name="payment_terms"]');
            const notes   = document.querySelector('textarea[name="notes"]');

            const modalInv   = document.getElementById('modal_invoice_date');
            const modalDue   = document.getElementById('modal_due_date');
            const modalTerms = document.getElementById('modal_payment_terms');
            const modalNotes = document.getElementById('modal_notes');

            if (modalInv && invDate)   modalInv.value   = invDate.value;
            if (modalDue && dueDate)   modalDue.value   = dueDate.value;
            if (modalTerms && terms)   modalTerms.value = terms.value;
            if (modalNotes && notes)   modalNotes.value = notes.value;

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeJobOrderModal() {
            const modal = document.getElementById('jobOrderModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Persist scroll position
            const scrollKey = 'invoice_edit_scroll';
            const savedScroll = sessionStorage.getItem(scrollKey);
            if (savedScroll !== null) {
                window.scrollTo(0, parseInt(savedScroll, 10));
                sessionStorage.removeItem(scrollKey);
            }

            function persistScrollPosition() {
                sessionStorage.setItem(scrollKey, String(window.scrollY || window.pageYOffset || 0));
            }

            document.querySelectorAll('form').forEach(function (formEl) {
                formEl.addEventListener('submit', persistScrollPosition);
            });

            window.submitInvoiceFormWithScroll = function (formEl) {
                if (!formEl) return;
                persistScrollPosition();
                formEl.submit();
            };

            // Customer typeahead (mirip create, tapi mungkin user jarang ganti customer saat edit)
            const customerList = @json($customerLookup);
            const searchInput = document.getElementById('customer_search');
            const hiddenId = document.getElementById('customer_id_input');
            const suggestionsBox = document.getElementById('customer_suggestions');

            function clearCustomerSuggestions() {
                if (!suggestionsBox) return;
                suggestionsBox.innerHTML = '';
                suggestionsBox.classList.add('hidden');
            }

            function renderCustomerSuggestions(items) {
                if (!suggestionsBox) return;
                if (!items.length) {
                    clearCustomerSuggestions();
                    return;
                }
                suggestionsBox.innerHTML = items.map(function (c) {
                    return '<button type="button" data-id=\"' + c.id + '\" class=\"w-full text-left px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800\">' +
                           c.name.replace(/</g, '&lt;') +
                           '</button>';
                }).join('');
                suggestionsBox.classList.remove('hidden');

                Array.prototype.forEach.call(suggestionsBox.querySelectorAll('button[data-id]'), function (btn) {
                    btn.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        const found = customerList.find(function (c) { return String(c.id) === String(id); });
                        if (!found) return;

                        if (hiddenId) hiddenId.value = found.id;
                        if (searchInput) searchInput.value = found.name;

                        const addrField = document.querySelector('textarea[name="customer_address"]');
                        const phoneField = document.querySelector('input[name="customer_phone"]');
                        const npwpField  = document.querySelector('input[name="customer_npwp"]');
                        if (addrField) addrField.value = found.address || '';
                        if (phoneField) phoneField.value = found.phone || '';
                        if (npwpField)  npwpField.value  = found.npwp || '';

                        clearCustomerSuggestions();
                        // Saat ganti customer, mungkin perlu reload halaman untuk refresh Job Order list?
                        // Tapi di edit mode, ini berisiko menghilangkan item yang sudah ada.
                        // Jadi kita biarkan saja, user harus manual refresh jika ingin ganti customer total.
                    });
                });
            }

            if (searchInput && suggestionsBox) {
                searchInput.addEventListener('input', function () {
                    const q = (this.value || '').trim().toLowerCase();
                    if (q.length < 2) {
                        clearCustomerSuggestions();
                        return;
                    }
                    const results = customerList.filter(function (c) {
                        return (c.name || '').toLowerCase().includes(q);
                    }).slice(0, 10);
                    renderCustomerSuggestions(results);
                });

                document.addEventListener('click', function (e) {
                    if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
                        clearCustomerSuggestions();
                    }
                });
            }

            // Subtotal auto-calc for existing items
            const itemContainers = document.querySelectorAll('.border.border-slate-200.rounded-lg.p-4.bg-slate-50');
            itemContainers.forEach(function(itemElement) {
                const qtyInput = itemElement.querySelector('input[name*="[quantity]"]');
                const priceInput = itemElement.querySelector('input[name*="[unit_price]"]');
                const subtotalDisplay = itemElement.querySelector('.item-subtotal');

                if (qtyInput && priceInput && subtotalDisplay) {
                    function updateSubtotal() {
                        const qty = parseFloat(qtyInput.value) || 0;
                        const price = parseFloat(priceInput.value) || 0;
                        const subtotal = qty * price;
                        subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    }
                    qtyInput.addEventListener('input', updateSubtotal);
                    priceInput.addEventListener('input', updateSubtotal);
                }
            });

            // PPN Calculation
            const subtotalHidden = document.getElementById('invoice_subtotal_value');
            const taxInput = document.getElementById('tax_amount_input');
            const pph23Input = document.getElementById('pph23_amount_input');
            const discountInput = document.getElementById('discount_amount_input');
            const taxCodeSelect = document.getElementById('tax_code_select_header');
            const btnCalcPph23 = document.getElementById('btn_calc_pph23');

            // Display Elements
            const displaySubtotal = document.getElementById('display_subtotal');
            const displayTax = document.getElementById('display_tax');
            const displayDiscount = document.getElementById('display_discount');
            const displayTotal = document.getElementById('display_total');
            const displayPph23 = document.getElementById('display_pph23');
            const displayNetPayable = document.getElementById('display_net_payable');

            function formatRupiah(num) {
                return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            // Function to calculate taxable vs non-taxable breakdown
            function updateTaxableBreakdown() {
                let taxableSubtotal = 0;
                let nontaxableSubtotal = 0;
                
                // Loop through all items
                document.querySelectorAll('[name^="items["][name$="][exclude_tax]"]').forEach(checkbox => {
                    const match = checkbox.name.match(/items\[(\d+)\]/);
                    if (!match) return;
                    
                    const index = match[1];
                    const qtyInput = document.querySelector(`[name="items[${index}][quantity]"]`);
                    const priceInput = document.querySelector(`[name="items[${index}][unit_price]"]`);
                    
                    if (qtyInput && priceInput) {
                        const qty = parseFloat(qtyInput.value) || 0;
                        const price = parseFloat(priceInput.value) || 0;
                        const amount = qty * price;
                        
                        if (checkbox.checked) {
                            nontaxableSubtotal += amount;
                        } else {
                            taxableSubtotal += amount;
                        }
                    }
                });
                
                // Update display
                const displayTaxableSubtotal = document.getElementById('display_taxable_subtotal');
                const displayNontaxableSubtotal = document.getElementById('display_nontaxable_subtotal');
                
                if (displayTaxableSubtotal) {
                    displayTaxableSubtotal.textContent = formatRupiah(taxableSubtotal);
                }
                if (displayNontaxableSubtotal) {
                    displayNontaxableSubtotal.textContent = formatRupiah(nontaxableSubtotal);
                }
                
                return { taxableSubtotal, nontaxableSubtotal };
            }

            function recalcTotals() {
                const subtotal = parseFloat(subtotalHidden.value || '0') || 0;
                let tax = parseFloat(taxInput.value || '0') || 0;
                const discount = parseFloat(discountInput.value || '0') || 0;
                const pph23 = parseFloat(pph23Input.value || '0') || 0;

                const total = subtotal + tax - discount;
                const netPayable = total - pph23;

                if(displaySubtotal) displaySubtotal.textContent = formatRupiah(subtotal);
                if(displayTax) displayTax.textContent = formatRupiah(tax);
                if(displayDiscount) displayDiscount.textContent = '- ' + formatRupiah(discount);
                if(displayTotal) displayTotal.textContent = formatRupiah(total);
                if(displayPph23) displayPph23.textContent = '- ' + formatRupiah(pph23);
                if(displayNetPayable) displayNetPayable.textContent = formatRupiah(netPayable);
                
                // Update taxable breakdown
                updateTaxableBreakdown();
            }

            function recalcPpn() {
                if (!subtotalHidden || !taxInput || !taxCodeSelect) return;
                
                // Get taxable subtotal only (exclude items with exclude_tax checked)
                const { taxableSubtotal } = updateTaxableBreakdown();
                const code = taxCodeSelect.value;

                let rate = 0.11; // Default
                if (code === '05') rate = 0.011;
                else if (code === '08' || code === '07') rate = 0.0;

                // DPP Nilai Lain Logic
                const dppNilaiLainCheckbox = document.getElementById('use_dpp_nilai_lain');
                const dppNilaiLainRow = document.getElementById('dpp_nilai_lain_row');
                const displayDppNilaiLain = document.getElementById('display_dpp_nilai_lain');

                let taxBase = taxableSubtotal; // Use taxable subtotal only
                if (dppNilaiLainCheckbox && dppNilaiLainCheckbox.checked) {
                    // Formula: Taxable Subtotal * 11/12
                    const dppNilaiLain = taxableSubtotal * (11 / 12);
                    taxBase = dppNilaiLain;

                    // Override rate to 12% as per regulation (effectively 11% of original)
                    rate = 0.12;

                    if (dppNilaiLainRow) dppNilaiLainRow.classList.remove('hidden');
                    if (displayDppNilaiLain) displayDppNilaiLain.textContent = formatRupiah(dppNilaiLain);
                } else {
                    if (dppNilaiLainRow) dppNilaiLainRow.classList.add('hidden');
                }

                if (rate !== null) {
                    const tax = taxBase * rate;
                    taxInput.value = tax.toFixed(2);
                }
                recalcTotals();
            }

            const dppCheckbox = document.getElementById('use_dpp_nilai_lain');
            if (dppCheckbox) {
                dppCheckbox.addEventListener('change', recalcPpn);
            }

            if (taxCodeSelect) {
                taxCodeSelect.addEventListener('change', recalcPpn);
            }

            if (taxInput) taxInput.addEventListener('input', recalcTotals);
            if (pph23Input) pph23Input.addEventListener('input', recalcTotals);
            if (discountInput) discountInput.addEventListener('input', recalcTotals);

            if (btnCalcPph23) {
                btnCalcPph23.addEventListener('click', function() {
                    // Calculate PPh23 from TAXABLE subtotal only (not all items)
                    const { taxableSubtotal } = updateTaxableBreakdown();
                    const pph = taxableSubtotal * 0.02;
                    if(pph23Input) {
                        pph23Input.value = pph.toFixed(2);
                        recalcTotals();
                    }
                });
            }
            
            // Add event listeners to all exclude_tax checkboxes
            document.querySelectorAll('[name^="items["][name$="][exclude_tax]"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    recalcPpn();
                    recalcTotals();
                });
            });

            // Initial calc
            updateTaxableBreakdown();
            recalcTotals();

            // Due date auto-calc
            const termInput = document.getElementById('payment_terms');
            const invoiceDateInput = document.querySelector('input[name="invoice_date"]');
            const dueDateInput = document.querySelector('input[name="due_date"]');

            function recalcDueDateFromTerm() {
                if (!termInput || !invoiceDateInput || !dueDateInput) return;
                const termDays = parseInt(termInput.value, 10);
                const invDateStr = invoiceDateInput.value;
                if (!termDays || !invDateStr) return;

                const base = new Date(invDateStr);
                if (isNaN(base.getTime())) return;

                base.setDate(base.getDate() + termDays);
                const yyyy = base.getFullYear();
                const mm = String(base.getMonth() + 1).padStart(2, '0');
                const dd = String(base.getDate()).padStart(2, '0');
                dueDateInput.value = `${yyyy}-${mm}-${dd}`;
            }

            if (termInput) termInput.addEventListener('input', recalcDueDateFromTerm);
            if (invoiceDateInput) invoiceDateInput.addEventListener('change', recalcDueDateFromTerm);
        });
    </script>
</div>
@endsection
