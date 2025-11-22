{{-- Preview & Items --}}
@if(isset($previewItems) && count($previewItems) > 0)
    @php 
        $subtotalPreview = 0;
        $lastItemType = null;
    @endphp
    <div class="space-y-2 mb-4">
        @foreach($previewItems as $item)
            @php
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotalPreview += $lineTotal;
                $currentItemType = $item['item_type'];
                $showSeparator = $lastItemType === 'job_order' && in_array($currentItemType, ['insurance_billable', 'additional_cost_billable']);
                $lastItemType = $currentItemType;
            @endphp
            
            {{-- Separator between main items and billable items --}}
            @if($showSeparator)
                <div class="flex items-center gap-3 py-2">
                    <div class="flex-1 border-t border-slate-300 dark:border-slate-600"></div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Biaya Tambahan (Billable)
                    </span>
                    <div class="flex-1 border-t border-slate-300 dark:border-slate-600"></div>
                </div>
            @endif
            
            <div class="flex justify-between items-start p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30">
                <div class="flex-1">
                    <div class="font-medium text-slate-900 dark:text-slate-100">
                        {{ $item['description'] }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Qty: {{ $item['quantity'] }} × Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                        @if(!empty($item['exclude_tax']))
                            <span class="ml-2 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs rounded">
                                NO PPN
                            </span>
                        @endif
                    </div>
                </div>
                <div class="font-bold text-indigo-600 dark:text-indigo-400 ml-4">
                    Rp {{ number_format($lineTotal, 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex justify-between items-center pt-3 border-t border-slate-200 dark:border-slate-700">
        <span class="font-semibold text-slate-700 dark:text-slate-300">Subtotal</span>
        <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
            Rp {{ number_format($subtotalPreview, 0, ',', '.') }}
        </span>
    </div>
    
    {{-- Add Item Button - Moved to top --}}
    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
        <button type="button" 
                onclick="addInvoiceItemRow()" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Baris Item
        </button>
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
            Tambahkan item custom (biaya admin, materai, dll)
        </p>
    </div>
    
    <div id="invoice-items-container" class="mt-3 space-y-3" data-next-index="{{ count($previewItems) }}">
        @php $lastEditItemType = null; @endphp
        @foreach($previewItems as $idx => $item)
            @php
                $currentEditItemType = $item['item_type'];
                $showEditSeparator = $lastEditItemType === 'job_order' && in_array($currentEditItemType, ['insurance_billable', 'additional_cost_billable']);
                $lastEditItemType = $currentEditItemType;
            @endphp
            
            {{-- Separator between main items and billable items --}}
            @if($showEditSeparator)
                <div class="flex items-center gap-3 py-3">
                    <div class="flex-1 border-t-2 border-dashed border-amber-300 dark:border-amber-700"></div>
                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider px-3 py-1 bg-amber-50 dark:bg-amber-900/20 rounded-full">
                        📋 Biaya Tambahan (Billable)
                    </span>
                    <div class="flex-1 border-t-2 border-dashed border-amber-300 dark:border-amber-700"></div>
                </div>
            @endif
            
            <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30 relative group">
                {{-- Delete Button --}}
                <button type="button" 
                        onclick="removeInvoiceItemRow(this)"
                        class="absolute top-2 right-2 p-1.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Hapus item ini">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                
                <input type="hidden" name="items[{{ $idx }}][job_order_id]" value="{{ $item['job_order_id'] }}">
                @if(isset($item['shipment_leg_id']))
                    <input type="hidden" name="items[{{ $idx }}][shipment_leg_id]" value="{{ $item['shipment_leg_id'] }}">
                @endif
                <input type="hidden" name="items[{{ $idx }}][item_type]" value="{{ $item['item_type'] }}">

                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            Deskripsi
                        </label>
                        <input type="text"
                               name="items[{{ $idx }}][description]"
                               value="{{ old('items.'.$idx.'.description', $item['description']) }}"
                               class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm"
                               required>
                        @error('items.'.$idx.'.description')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            Qty
                        </label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               name="items[{{ $idx }}][quantity]"
                               value="{{ old('items.'.$idx.'.quantity', $item['quantity']) }}"
                               class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm"
                               required>
                        @error('items.'.$idx.'.quantity')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            Unit Price
                        </label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="items[{{ $idx }}][unit_price]"
                               value="{{ old('items.'.$idx.'.unit_price', $item['unit_price']) }}"
                               class="w-full rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm"
                               required>
                        @error('items.'.$idx.'.unit_price')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            Subtotal
                        </label>
                        <div class="w-full rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100 item-subtotal">
                            Rp {{ number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                
                {{-- Checkbox untuk Exclude Tax --}}
                <div class="mt-3 flex items-center gap-2">
                    <input type="checkbox" 
                           name="items[{{ $idx }}][exclude_tax]" 
                           id="exclude_tax_{{ $idx }}"
                           value="1"
                           {{ !empty($item['exclude_tax']) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="exclude_tax_{{ $idx }}" class="text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                        <span class="font-medium">Exclude dari PPN</span>
                        <span class="text-slate-500 dark:text-slate-500"> (Item ini tidak dikenakan pajak)</span>
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tax & Discount --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Tax Amount (PPN)
            </label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="tax_amount"
                   id="tax_amount_input"
                   value="{{ old('tax_amount', 0) }}"
                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
            <p class="text-xs text-slate-500 mt-1">
                <span class="font-medium">Otomatis dihitung</span> dari item yang <strong>tidak di-exclude</strong> dari PPN.
            </p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                PPh 23 Amount
            </label>
            <div class="flex gap-2">
                <input type="number"
                       step="0.01"
                       min="0"
                       name="pph23_amount"
                       id="pph23_amount_input"
                       value="{{ old('pph23_amount', 0) }}"
                       class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
                <button type="button" id="btn_calc_pph23" class="px-3 py-2 bg-slate-200 dark:bg-slate-700 rounded text-xs font-medium hover:bg-slate-300 dark:hover:bg-slate-600">
                    Hitung 2%
                </button>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <input type="checkbox" 
                       name="show_pph23" 
                       id="show_pph23_checkbox"
                       value="1"
                       {{ old('show_pph23') ? 'checked' : '' }}
                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="show_pph23_checkbox" class="text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                    Tampilkan PPh 23 di Invoice
                </label>
            </div>
            <p class="text-xs text-slate-500 mt-1">Potongan PPh 23 (jika ada).</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Discount Amount
            </label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="discount_amount"
                   id="discount_amount_input"
                   value="{{ old('discount_amount', 0) }}"
                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
        </div>
    </div>

    {{-- Summary --}}
    <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-lg p-4 space-y-2">
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
        
        <div class="flex justify-between items-center text-sm font-semibold">
            <span class="text-slate-700 dark:text-slate-300">Subtotal</span>
            <input type="hidden" id="invoice_subtotal_value" name="subtotal" value="{{ $subtotalPreview ?? 0 }}">
            <span class="text-slate-900 dark:text-slate-100" id="display_subtotal">
                Rp {{ number_format($subtotalPreview ?? 0, 0, ',', '.') }}
            </span>
        </div>

        {{-- DPP Nilai Lain Option --}}
        <div class="flex justify-between items-center text-sm py-1">
            <div class="flex items-center gap-2">
                <input type="checkbox" 
                       id="use_dpp_nilai_lain" 
                       name="use_dpp_nilai_lain" 
                       value="1" 
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
            <span class="text-slate-900 dark:text-slate-100" id="display_tax">
                Rp 0
            </span>
        </div>
        <div class="flex justify-between items-center text-sm text-red-600 dark:text-red-400">
            <span>Diskon</span>
            <span id="display_discount">
                - Rp 0
            </span>
        </div>
        <div class="flex justify-between items-center text-base font-bold border-t border-slate-100 dark:border-slate-800 pt-2">
            <span class="text-slate-800 dark:text-slate-200">Total Tagihan (Receivable)</span>
            <span class="text-indigo-600 dark:text-indigo-400" id="display_total">
                Rp {{ number_format($subtotalPreview, 0, ',', '.') }}
            </span>
        </div>
        <div class="flex justify-between items-center text-sm text-amber-600 dark:text-amber-400 pt-1">
            <span>Potongan PPh 23 (Estimasi)</span>
            <span id="display_pph23">- Rp 0</span>
        </div>
        <div class="flex justify-between items-center text-sm font-semibold text-emerald-600 dark:text-emerald-400 pt-1">
            <span>Sisa Tagihan (Net Payable)</span>
            <span id="display_net_payable">Rp {{ number_format($subtotalPreview, 0, ',', '.') }}</span>
        </div>
    </div>
    {{-- Submit --}}
    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
        <x-button :href="route('invoices.index')" variant="outline">
            Batal
        </x-button>
        <button type="button" id="btn_preview_invoice" class="inline-flex items-center px-4 py-2 border border-indigo-600 dark:border-indigo-500 rounded-lg text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Preview Invoice
        </button>
        <x-button type="submit" variant="primary">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Simpan Invoice
        </x-button>
    </div>
@else
    {{-- Empty state when belum ada item --}}
    <div class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">
            Belum Ada Item
        </h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
            Setelah memilih customer dan memilih Job Order di atas lalu klik
            <span class="font-semibold">Preview Items</span>,
            item dari Job Order yang dipilih akan otomatis muncul di sini.
        </p>
    </div>
@endif
