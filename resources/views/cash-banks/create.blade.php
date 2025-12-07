@extends('layouts.app', ['title' => 'Transaksi Kas/Bank'])

@push('styles')
<style>
.filter-status-btn {
    background-color: transparent;
    border: 1px solid rgb(203 213 225);
    color: rgb(100 116 139);
    transition: all 0.2s;
}

.filter-status-btn:hover {
    background-color: rgb(241 245 249);
    border-color: rgb(148 163 184);
}

.filter-status-btn.active {
    background-color: rgb(59 130 246);
    border-color: rgb(59 130 246);
    color: white;
}

.dark .filter-status-btn {
    border-color: rgb(51 65 85);
    color: rgb(148 163 184);
}

.dark .filter-status-btn:hover {
    background-color: rgb(30 41 59);
    border-color: rgb(71 85 105);
}

.dark .filter-status-btn.active {
    background-color: rgb(59 130 246);
    border-color: rgb(59 130 246);
    color: white;
}

/* Custom Scrollbar for Vendor Bills */
#vendor-bills-scroll::-webkit-scrollbar {
    height: 8px;
}

#vendor-bills-scroll::-webkit-scrollbar-track {
    background: rgb(241 245 249);
    border-radius: 4px;
}

#vendor-bills-scroll::-webkit-scrollbar-thumb {
    background: rgb(148 163 184);
    border-radius: 4px;
    transition: background 0.2s;
}

#vendor-bills-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(100 116 139);
}

.dark #vendor-bills-scroll::-webkit-scrollbar-track {
    background: rgb(30 41 59);
}

.dark #vendor-bills-scroll::-webkit-scrollbar-thumb {
    background: rgb(71 85 105);
}

.dark #vendor-bills-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(100 116 139);
}

/* Custom Scrollbar for Invoices */
#invoices-scroll::-webkit-scrollbar {
    height: 8px;
}

#invoices-scroll::-webkit-scrollbar-track {
    background: rgb(241 245 249);
    border-radius: 4px;
}

#invoices-scroll::-webkit-scrollbar-thumb {
    background: rgb(148 163 184);
    border-radius: 4px;
    transition: background 0.2s;
}

#invoices-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(100 116 139);
}

.dark #invoices-scroll::-webkit-scrollbar-track {
    background: rgb(30 41 59);
}

.dark #invoices-scroll::-webkit-scrollbar-thumb {
    background: rgb(71 85 105);
}

.dark #invoices-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(100 116 139);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
</style>
@endpush

@section('content')
<form method="post" action="{{ route('cash-banks.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="payment_request_id" value="{{ $prefill['payment_request_id'] ?? '' }}">
    <x-card title="Header">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ now()->format('Y-m-d') }}"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required
                >
            </div>
            <div>
                <label class="block text-sm mb-1">Akun Kas/Bank</label>
                <select
                    name="cash_bank_account_id"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required
                >
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Jenis</label>
                <select
                    name="jenis"
                    id="jenis"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required
                >
                    <option value="cash_in" {{ ($prefill['sumber'] ?? '')==='customer_payment' ? 'selected' : '' }}>Cash In</option>
                    <option value="cash_out" {{ in_array(($prefill['sumber'] ?? ''), ['vendor_payment','expense']) ? 'selected' : '' }}>Cash Out</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm mb-1">Sumber <span class="text-red-500">*</span></label>
                <select
                    name="sumber"
                    id="sumber"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required
                    onchange="toggleFields()"
                >
                            <option value="customer_payment" @selected(old('sumber', $prefill['sumber'] ?? '') == 'customer_payment')>Customer Payment</option>
                            <option value="vendor_payment" @selected(old('sumber', $prefill['sumber'] ?? '') == 'vendor_payment')>Vendor Payment</option>
                            <option value="expense" @selected(old('sumber', $prefill['sumber'] ?? '') == 'expense')>Expense</option>
                            <option value="other_in" @selected(old('sumber', $prefill['sumber'] ?? '') == 'other_in')>Other Income</option>
                            <option value="other_out" @selected(old('sumber', $prefill['sumber'] ?? '') == 'other_out')>Other Expense</option>
                            <option value="uang_jalan" @selected(old('sumber', $prefill['sumber'] ?? '') == 'uang_jalan')>Driver Advance / Savings</option>
                        </select>
                        @error('sumber')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                          @enderror
            </div>
            <div id="invoice_field" style="display: none;">
                <label class="block text-sm mb-1">Invoices</label>
                <button
                    type="button"
                    onclick="openInvoiceModal()"
                    class="w-full btn-primary justify-center"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pilih Invoices
                </button>
            </div>
            <div id="vendor_bill_field" style="display: none;">
                <label class="block text-sm mb-1">Vendor Bills</label>
                <button
                    type="button"
                    onclick="openVendorBillModal()"
                    class="w-full btn-primary justify-center"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pilih Vendor Bills
                </button>
            </div>
            <div id="coa_field" style="display: none;">
                <label class="block text-sm mb-1" id="coa_label">Akun Biaya</label>
                <select
                    name="coa_id"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
                    <option value="">- Pilih Akun -</option>
                    @foreach($coas as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                      @endforeach
                  </select>
              </div>
              <div id="driver_advance_field" style="display: none;">
                  <label class="block text-sm mb-1">Driver Advance</label>
                  <button
                      type="button"
                      onclick="openDriverAdvanceModal()"
                      class="w-full btn-primary justify-center"
                  >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      Select Driver Advance
                  </button>
            </div>
        </div>
        {{-- Selected Invoices Display (Full Width) --}}
        <div id="selected_invoices" class="mt-4" style="display: none;"></div>
        {{-- Selected Vendor Bills Display (Full Width) --}}
        <div id="selected_vendor_bills" class="mt-4" style="display: none;"></div>
        {{-- Selected Driver Advances Display (Full Width) --}}
        <div id="selected_driver_advances" class="mt-4" style="display: none;"></div>
        
        {{-- Financial Fields: Nominal, PPh23, Admin, Total (1 Row) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <div>
                <label class="block text-sm mb-1">Nominal <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    id="amount_display"
                    placeholder="1.000.000"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required
                >
                <input type="hidden" name="amount" id="amount_input" value="{{ $prefill['amount'] ?? '' }}">
            </div>
            
            <div id="pph23_field" style="display: none;">
                <label class="block text-sm mb-1">PPh 23</label>
                <div class="relative">
                    <input
                        type="text"
                        id="pph23_display"
                        placeholder="0"
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >
                    <input type="hidden" name="withholding_pph23" id="pph23_input" value="{{ old('withholding_pph23', '0') }}">
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <button type="button" onclick="autoCalculatePPh23()" class="text-xs px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200">
                        Hitung 2%
                    </button>
                    <button type="button" onclick="clearPPh23()" class="text-xs px-1.5 py-0.5 text-slate-500 hover:text-slate-700">
                        Clear
                    </button>
                </div>
            </div>
            
            <div>
                <label class="block text-sm mb-1">Biaya Admin</label>
                <input
                    type="text"
                    id="admin_fee_display"
                    placeholder="0"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
                <input type="hidden" name="admin_fee" id="admin_fee_input" value="0">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Opsional</p>
            </div>
            
            <div>
                <label class="block text-sm mb-1 flex items-center gap-1">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span id="total_bank_label">Total di Bank</span>
                </label>
                <input type="text" id="total_bank_display" readonly class="w-full rounded bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 px-2 py-2 font-bold text-blue-600 dark:text-blue-400 cursor-not-allowed" value="Rp 0">
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1" id="total_bank_hint">Auto-calculate</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm mb-1" id="recipient_label">Nama Penerima/Pengirim</label>
                <input
                    type="text"
                    name="recipient_name"
                    id="recipient_name"
                    value="{{ $prefill['recipient_name'] ?? '' }}"
                    placeholder="Nama orang/perusahaan"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" id="recipient_hint">Nama yang menerima/mengirim</p>
            </div>
            <div>
                <label class="block text-sm mb-1">No. Referensi</label>
                <input
                    type="text"
                    name="reference_number"
                    placeholder="Nomor transfer, cek, dll"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Nomor bukti transfer</p>
            </div>
            <div>
        </div>
        <div class="mt-4">
            <label class="block text-sm mb-1">Deskripsi</label>
            <textarea
                id="description"
                name="description"
                rows="3"
                placeholder="Catatan tambahan..."
                class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
            >{{ $prefill['description'] ?? '' }}</textarea>
        </div>
    </x-card>

    <div class="flex justify-end gap-2">
        <x-button :href="route('cash-banks.index')" variant="outline" size="sm">
            Batal
        </x-button>
        <x-button type="submit" variant="primary" size="sm">
            Simpan Transaksi
        </x-button>
    </div>
</form>

{{-- Modal Invoices --}}
<div id="invoiceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">Pilih Invoices</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pilih satu atau lebih invoices untuk pembayaran</p>
            </div>
            <button type="button" onclick="closeInvoiceModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Search Box --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       id="invoiceSearch" 
                       placeholder="Search by invoice number, customer name, or job order..." 
                       class="w-full pl-10 pr-10 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       onkeyup="searchInvoices()">
                <button type="button" 
                        id="clearInvoiceSearch" 
                        onclick="clearInvoiceSearch()" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span id="invoiceSearchResultCount">{{ count($invoices) }}</span> invoices found
                </p>
                <div class="flex gap-2">
                    <button type="button" onclick="filterInvoiceByStatus('all')" class="filter-status-btn active px-2 py-1 text-xs rounded" data-status="all">All</button>
                    <button type="button" onclick="filterInvoiceByStatus('sent')" class="filter-status-btn px-2 py-1 text-xs rounded" data-status="sent">Sent</button>
                    <button type="button" onclick="filterInvoiceByStatus('partial')" class="filter-status-btn px-2 py-1 text-xs rounded" data-status="partial">Partial</button>
                </div>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700" id="invoiceTable">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                <input type="checkbox" id="selectAllInvoices" onchange="toggleSelectAllInvoices()" class="rounded border-slate-300">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Job Order</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($invoices as $inv)
                                @php
                                    // Get job number from invoice items if available
                                    $jobNumber = '-';
                                    if ($inv->items && $inv->items->count() > 0) {
                                        $firstItem = $inv->items->first();
                                        if ($firstItem && $firstItem->description) {
                                            // Try to extract JO number from description if it exists
                                            preg_match('/JO[- ]?(\d+[-\d]*)/', $firstItem->description, $matches);
                                            $jobNumber = $matches[0] ?? '-';
                                        }
                                    }
                                    $searchText = strtolower($inv->invoice_number . ' ' . ($inv->customer->name ?? '') . ' ' . $jobNumber);
                                    $isPaid = $inv->status === 'paid';
                                    $outstanding = $inv->total_amount - $inv->paid_amount;
                                    // DPP = subtotal (before tax)
                                    $dpp = $inv->subtotal ?? $outstanding;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 invoice-row {{ $isPaid ? 'opacity-50' : '' }}" 
                                    data-search-text="{{ $searchText }}"
                                    data-status="{{ $inv->status }}">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" 
                                               class="invoice-checkbox rounded border-slate-300" 
                                               value="{{ $inv->id }}"
                                               data-number="{{ $inv->invoice_number }}"
                                               data-customer="{{ $inv->customer->name ?? '-' }}"
                                               data-amount="{{ $outstanding }}"
                                               data-dpp="{{ $dpp }}"
                                               data-pph23="{{ $inv->pph23_amount ?? 0 }}"
                                               data-date="{{ $inv->invoice_date?->format('d/m/Y') ?? '-' }}"
                                               data-job="{{ $jobNumber }}"
                                               onchange="updateSelectedInvoices()"
                                               {{ $isPaid ? 'disabled' : '' }}>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $inv->invoice_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $inv->customer->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $inv->invoice_date?->format('d/m/Y') ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $jobNumber }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-mono text-sm text-slate-900 dark:text-slate-100">
                                        Rp {{ number_format($outstanding, 0, ',', '.') }}
                                    </div>
                                    @if($inv->paid_amount > 0)
                                        <div class="text-xs text-slate-500">Paid: Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        {{ $inv->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                           ($inv->status === 'partial' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                           'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-3">
                    <div>
                        <span id="selectedInvoiceCount">0</span> invoices dipilih
                        <span class="mx-2">•</span>
                        Total: <span class="font-bold text-slate-900 dark:text-slate-100" id="selectedInvoiceTotal">Rp 0</span>
                    </div>
                    <div id="selectedCustomerInfo" class="hidden px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                        Customer: -
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Hanya bisa pilih invoices dari customer yang sama
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeInvoiceModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded hover:bg-slate-50 dark:hover:bg-slate-800">
                    Batal
                </button>
                <button type="button" onclick="confirmInvoiceSelection()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Konfirmasi Pilihan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Vendor Bills --}}
<div id="vendorBillModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">Pilih Vendor Bills</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pilih satu atau lebih vendor bills untuk dibayar</p>
            </div>
            <button type="button" onclick="closeVendorBillModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Search Box --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       id="vendorBillSearch" 
                       placeholder="Search by vendor bill number, vendor name, or job order..." 
                       class="w-full pl-10 pr-10 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       onkeyup="searchVendorBills()">
                <button type="button" 
                        id="clearSearch" 
                        onclick="clearVendorBillSearch()" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span id="searchResultCount">{{ count($vendorBills) }}</span> vendor bills found
                </p>
                <div class="flex gap-2">
                    <button type="button" onclick="filterByStatus('all')" class="filter-status-btn active px-2 py-1 text-xs rounded" data-status="all">All</button>
                    <button type="button" onclick="filterByStatus('pending')" class="filter-status-btn px-2 py-1 text-xs rounded" data-status="pending">Pending</button>
                    <button type="button" onclick="filterByStatus('paid')" class="filter-status-btn px-2 py-1 text-xs rounded" data-status="paid">Paid</button>
                </div>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700" id="vendorBillTable">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-slate-300">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Vendor Bill</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Job Order</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($vendorBills as $vb)
                            @php
                                $jobNumbers = $vb->items->pluck('shipmentLeg.jobOrder')->filter()->unique('id')->pluck('job_number')->join(', ');
                                $searchText = strtolower($vb->vendor_bill_number . ' ' . ($vb->vendor->name ?? '') . ' ' . $jobNumbers);
                                $isPaid = $vb->status === 'paid';
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 vendor-bill-row {{ $isPaid ? 'opacity-50' : '' }}" 
                                data-search-text="{{ $searchText }}"
                                data-status="{{ $vb->status }}">
                                <td class="px-4 py-3">
                                    <input type="checkbox" 
                                           class="vendor-bill-checkbox rounded border-slate-300" 
                                           value="{{ $vb->id }}"
                                           data-number="{{ $vb->vendor_bill_number }}"
                                           data-vendor="{{ $vb->vendor->name ?? '-' }}"
                                           data-amount="{{ $vb->total_amount }}"
                                           data-pph23="{{ $vb->pph23 ?? 0 }}"
                                           data-date="{{ $vb->bill_date?->format('d/m/Y') ?? '-' }}"
                                           data-job="{{ $jobNumbers ?: '-' }}"
                                           onchange="updateSelectedVendorBills()"
                                           {{ $isPaid ? 'disabled' : '' }}>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $vb->vendor_bill_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $vb->vendor->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $vb->bill_date?->format('d/m/Y') ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">
                                        {{ $jobNumbers ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-mono text-sm text-slate-900 dark:text-slate-100">
                                        Rp {{ number_format($vb->total_amount, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        {{ $vb->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                        {{ ucfirst($vb->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-3">
                    <div>
                        <span id="selectedCount">0</span> vendor bills dipilih
                        <span class="mx-2">•</span>
                        Total: <span class="font-bold text-slate-900 dark:text-slate-100" id="selectedTotal">Rp 0</span>
                    </div>
                    <div id="selectedVendorInfo" class="hidden px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                        Vendor: -
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Hanya bisa pilih vendor bills dari vendor yang sama
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeVendorBillModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded hover:bg-slate-50 dark:hover:bg-slate-800">
                    Batal
                </button>
                <button type="button" onclick="confirmVendorBillSelection()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Konfirmasi Pilihan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Driver Advances --}}
<div id="driverAdvanceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">Select Driver Advance</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Select one or more driver advances for payment</p>
            </div>
            <button type="button" onclick="closeDriverAdvanceModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Search Box --}}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       id="driverAdvanceSearch" 
                       placeholder="Search by advance number, driver name, or job order..." 
                       class="w-full pl-10 pr-10 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       onkeyup="searchDriverAdvances()">
                <button type="button" 
                        id="clearDriverAdvanceSearch" 
                        onclick="clearDriverAdvanceSearch()" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span id="driverAdvanceSearchResultCount">{{ count($driverAdvances) }}</span> driver advances found
                </p>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700" id="driverAdvanceTable">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                <input type="checkbox" id="selectAllDriverAdvances" onchange="toggleSelectAllDriverAdvances()" class="rounded border-slate-300">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Advance #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Driver</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Job Order</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Remaining</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($driverAdvances as $da)
                            @php
                                $jobNumber = $da->shipmentLeg->jobOrder->job_number ?? '-';
                                $searchText = strtolower($da->advance_number . ' ' . ($da->driver->name ?? '') . ' ' . $jobNumber);
                                $remaining = $da->remaining_to_request;
                                
                                // Get net amount from mainCost if available
                                $mainCost = $da->shipmentLeg->mainCost ?? null;
                                $grossAmount = $mainCost->uang_jalan ?? $da->amount;
                                $savingsDeduction = $mainCost->driver_savings_deduction ?? $da->deduction_savings ?? 0;
                                $guaranteeDeduction = $mainCost->driver_guarantee_deduction ?? $da->deduction_guarantee ?? 0;
                                $netAmount = $grossAmount - $savingsDeduction - $guaranteeDeduction;
                                $hasDeductions = ($savingsDeduction > 0 || $guaranteeDeduction > 0);
                                
                                // Get additional data for description
                                $jobOrder = $da->shipmentLeg->jobOrder;
                                $customer = $jobOrder->customer->name ?? 'N/A';
                                $plateNumber = $da->shipmentLeg->truck->plate_number ?? 'N/A';
                                $origin = $jobOrder->origin ?? 'N/A';
                                $destination = $jobOrder->destination ?? 'N/A';
                                
                                // Get cargo details (first item)
                                $cargoItem = $jobOrder->items->first();
                                $cargoQty = $cargoItem ? $cargoItem->quantity : $da->shipmentLeg->quantity;
                                $cargoUnit = 'unit'; // Default unit since job_order_items doesn't have unit column
                                $cargoDesc = $cargoItem ? $cargoItem->cargo_type : 'barang';
                                
                                // Determine payment type (DP or Pelunasan)
                                $paymentType = ($da->dp_amount > 0 && $da->status === 'dp_paid') ? 'Pelunasan' : 'DP';
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 driver-advance-row" 
                                data-search-text="{{ $searchText }}">
                                <td class="px-4 py-3">
                                    <input type="checkbox" 
                                           class="driver-advance-checkbox rounded border-slate-300" 
                                           value="{{ $da->id }}"
                                           data-number="{{ $da->advance_number }}"
                                           data-driver="{{ $da->driver->name ?? '-' }}"
                                           data-amount="{{ $netAmount }}"
                                           data-gross="{{ $grossAmount }}"
                                           data-savings="{{ $savingsDeduction }}"
                                           data-guarantee="{{ $guaranteeDeduction }}"
                                           data-date="{{ $da->advance_date?->format('d/m/Y') ?? '-' }}"
                                           data-job="{{ $jobNumber }}"
                                           data-customer="{{ $customer }}"
                                           data-plate="{{ $plateNumber }}"
                                           data-origin="{{ $origin }}"
                                           data-destination="{{ $destination }}"
                                           data-cargo-qty="{{ $cargoQty }}"
                                           data-cargo-unit="{{ $cargoUnit }}"
                                           data-cargo-desc="{{ $cargoDesc }}"
                                           data-payment-type="{{ $paymentType }}"
                                           onchange="updateSelectedDriverAdvances()">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $da->advance_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $da->driver->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $da->advance_date?->format('d/m/Y') ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ $jobNumber }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-mono text-sm font-bold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($netAmount, 0, ',', '.') }}
                                    </div>
                                    @if($hasDeductions)
                                        <div class="text-xs text-slate-500 mt-1">
                                            Gross: Rp {{ number_format($grossAmount, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-red-500">
                                            Deductions: Rp {{ number_format($savingsDeduction + $guaranteeDeduction, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-500">No deductions</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ ucfirst($da->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-3">
                    <div>
                        <span id="selectedDriverAdvanceCount">0</span> driver advances selected
                        <span class="mx-2">•</span>
                        Total: <span class="font-bold text-slate-900 dark:text-slate-100" id="selectedDriverAdvanceTotal">Rp 0</span>
                    </div>
                    <div id="selectedDriverInfo" class="hidden px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                        Driver: -
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    You can select multiple driver advances from the same driver
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeDriverAdvanceModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded hover:bg-slate-50 dark:hover:bg-slate-800">
                    Cancel
                </button>
                <button type="button" onclick="confirmDriverAdvanceSelection()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Confirm Selection
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Confirmation Helper --}}
<div id="confirmationModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[10000] hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl max-w-md w-full p-6 animate-fade-in-up">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2" id="confirmTitle">Konfirmasi</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed" id="confirmMessage">
                    Are you sure?
                </p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeConfirmationModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm font-medium">
                Batal
            </button>
            <button type="button" id="confirmActionBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium shadow-lg shadow-blue-500/30">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Flag to track if PPh23 comes from invoice
    let isPPh23FromInvoice = false;

    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function setupMoneyInput(displayId, hiddenId, callback) {
        const displayEl = document.getElementById(displayId);
        const hiddenEl = document.getElementById(hiddenId);

        if (!displayEl || !hiddenEl) {
            return;
        }

        displayEl.addEventListener('input', function() {
            let value = this.value.replace(/\./g, ''); // Remove dots
            value = value.replace(/[^\d]/g, ''); // Only digits

            if (value) {
                this.value = formatNumber(value);
                hiddenEl.value = value;
            } else {
                this.value = '';
                hiddenEl.value = '0';
            }

            if (callback) {
                callback();
            }
            
            // If user manually edits amount, reset the "from invoice" flag
            if (displayId === 'amount_display') {
                isPPh23FromInvoice = false;
            }
        });

        if (hiddenEl.value && parseFloat(hiddenEl.value) > 0) {
            displayEl.value = formatNumber(hiddenEl.value);
        }
    }

    setupMoneyInput('amount_display', 'amount_input', function() {
        // Auto-calculate PPh23 if enabled
        if (typeof autoPPh23Enabled !== 'undefined' && autoPPh23Enabled) {
            const amount = parseFloat(document.getElementById('amount_input').value) || 0;
            if (amount > 0) {
                const pph23 = amount * 0.02;
                document.getElementById('pph23_display').value = formatNumber(pph23.toFixed(0));
                document.getElementById('pph23_input').value = pph23;
            }
        }
        updateTotalBank();
    });
    
    setupMoneyInput('pph23_display', 'pph23_input', updateTotalBank);
    setupMoneyInput('admin_fee_display', 'admin_fee_input', updateTotalBank);
    
    // Initialize field visibility
    toggleFields();
    
    // Move modals to body to ensure they cover sidebar
    const invoiceModal = document.getElementById('invoiceModal');
    const vendorBillModal = document.getElementById('vendorBillModal');
    const driverAdvanceModal = document.getElementById('driverAdvanceModal');
    
    if (invoiceModal && invoiceModal.parentElement !== document.body) {
        document.body.appendChild(invoiceModal);
    }
    
    if (vendorBillModal && vendorBillModal.parentElement !== document.body) {
        document.body.appendChild(vendorBillModal);
    }
    
    if (driverAdvanceModal && driverAdvanceModal.parentElement !== document.body) {
        document.body.appendChild(driverAdvanceModal);
    }
});

function toggleFields() {
    const sumber = document.getElementById('sumber').value;
    
    // Hide all conditional fields first
    document.getElementById('invoice_field').style.display = 'none';
    document.getElementById('vendor_bill_field').style.display = 'none';
    document.getElementById('coa_field').style.display = 'none';
    document.getElementById('driver_advance_field').style.display = 'none';
    document.getElementById('pph23_field').style.display = 'none';
    
    // Update jenis based on sumber
    const jenisSelect = document.getElementById('jenis');
    
    // Update recipient label based on jenis
    const recipientLabel = document.getElementById('recipient_label');
    const recipientHint = document.getElementById('recipient_hint');
    
    // Show relevant fields based on sumber
    if (sumber === 'customer_payment') {
        document.getElementById('invoice_field').style.display = 'block';
        document.getElementById('pph23_field').style.display = 'block';
        jenisSelect.value = 'cash_in';
        recipientLabel.textContent = 'Nama Pengirim';
        recipientHint.textContent = 'Nama customer/orang yang mengirim uang';
    } else if (sumber === 'vendor_payment') {
        document.getElementById('vendor_bill_field').style.display = 'block';
        jenisSelect.value = 'cash_out';
        recipientLabel.textContent = 'Nama Penerima';
        recipientHint.textContent = 'Nama vendor/orang yang menerima uang';
    } else if (sumber === 'uang_jalan') {
        document.getElementById('driver_advance_field').style.display = 'block';
        jenisSelect.value = 'cash_out';
        recipientLabel.textContent = 'Dibayarkan Kepada (Supir)';
        recipientHint.textContent = 'Nama supir yang mencairkan tabungan';
    } else if (sumber === 'expense') {
        document.getElementById('coa_field').style.display = 'block';
        document.getElementById('coa_label').textContent = 'Akun Biaya' + (sumber === 'expense' ? '' : ' (Wajib)');
        jenisSelect.value = 'cash_out';
        recipientLabel.textContent = 'Nama Penerima';
        recipientHint.textContent = 'Nama orang/perusahaan yang menerima pembayaran';
    } else if (sumber === 'other_in') {
        document.getElementById('coa_field').style.display = 'block';
        document.getElementById('coa_label').textContent = 'Akun Pendapatan (Opsional)';
        jenisSelect.value = 'cash_in';
        recipientLabel.textContent = 'Nama Pengirim';
        recipientHint.textContent = 'Nama orang/perusahaan yang mengirim uang';
    } else if (sumber === 'other_out') {
        document.getElementById('coa_field').style.display = 'block';
        document.getElementById('coa_label').textContent = 'Akun Biaya (Opsional)';
        jenisSelect.value = 'cash_out';
        recipientLabel.textContent = 'Nama Penerima';
        recipientHint.textContent = 'Nama orang/perusahaan yang menerima uang';
    }
}

function populateInvoiceAmount() {
    const select = document.getElementById('invoice_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const amount = selectedOption.getAttribute('data-amount');
        document.getElementById('amount_input').value = amount;
        document.getElementById('amount_display').value = formatNumber(amount);
    }
}

function populateVendorBillAmount() {
    const select = document.getElementById('vendor_bill_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const amount = selectedOption.getAttribute('data-amount');
        document.getElementById('amount_input').value = amount;
        document.getElementById('amount_display').value = formatNumber(amount);
    }
}


function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ===== VENDOR BILL MODAL FUNCTIONS =====
let selectedVendorBills = [];

function openVendorBillModal() {
    document.getElementById('vendorBillModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeVendorBillModal() {
    document.getElementById('vendorBillModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.vendor-bill-checkbox');
    
    checkboxes.forEach(checkbox => {
        // Hanya checklist checkbox yang visible (tidak di-hide) dan tidak disabled
        const row = checkbox.closest('tr');
        const isVisible = row.style.display !== 'none';
        const isEnabled = !checkbox.disabled;
        
        if (isVisible && isEnabled) {
            checkbox.checked = selectAll.checked;
        }
    });
    
    updateSelectedVendorBills();
}

function updateSelectedVendorBills() {
    const checkboxes = document.querySelectorAll('.vendor-bill-checkbox:checked');
    const allCheckboxes = document.querySelectorAll('.vendor-bill-checkbox');
    const count = checkboxes.length;
    let total = 0;
    let selectedVendor = null;
    
    // Get vendor dari checkbox pertama yang dipilih
    if (checkboxes.length > 0) {
        selectedVendor = checkboxes[0].getAttribute('data-vendor');
    }
    
    // Update checkbox states based on vendor selection
    allCheckboxes.forEach(checkbox => {
        const checkboxVendor = checkbox.getAttribute('data-vendor');
        const row = checkbox.closest('tr');
        const isPaid = row.getAttribute('data-status') === 'paid';
        
        // Skip if already paid (permanently disabled)
        if (isPaid) {
            return;
        }
        
        if (selectedVendor && checkboxVendor !== selectedVendor && !checkbox.checked) {
            // Disable checkbox jika vendor berbeda
            checkbox.disabled = true;
            row.classList.add('opacity-50', 'cursor-not-allowed');
            row.style.pointerEvents = 'none';
        } else {
            // Enable checkbox jika vendor sama atau tidak ada yang dipilih
            checkbox.disabled = false;
            row.classList.remove('opacity-50', 'cursor-not-allowed');
            row.style.pointerEvents = 'auto';
        }
    });
    
    // Calculate total from CHECKED checkboxes only
    checkboxes.forEach(checkbox => {
        total += parseFloat(checkbox.getAttribute('data-amount'));
    });
    
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('selectedTotal').textContent = 'Rp ' + formatNumber(total.toFixed(0));
    
    // Update info vendor yang dipilih
    const vendorInfo = document.getElementById('selectedVendorInfo');
    if (vendorInfo) {
        if (selectedVendor) {
            vendorInfo.textContent = 'Vendor: ' + selectedVendor;
            vendorInfo.classList.remove('hidden');
        } else {
            vendorInfo.classList.add('hidden');
        }
    }
    
    // Update status checkbox "Select All"
    const selectAllCheckbox = document.getElementById('selectAll');
    const visibleEnabledCheckboxes = Array.from(allCheckboxes).filter(cb => {
        const row = cb.closest('tr');
        return row.style.display !== 'none' && !cb.disabled;
    });
    
    const allVisibleChecked = visibleEnabledCheckboxes.length > 0 && 
                              visibleEnabledCheckboxes.every(cb => cb.checked);
    
    selectAllCheckbox.checked = allVisibleChecked;
}

function confirmVendorBillSelection() {
    const checkboxes = document.querySelectorAll('.vendor-bill-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Silakan pilih minimal 1 vendor bill');
        return;
    }
    
    selectedVendorBills = [];
    let totalAmount = 0;
    let firstVendor = null;
    
    // Validasi: semua vendor harus sama
    for (let checkbox of checkboxes) {
        const vendor = checkbox.getAttribute('data-vendor');
        
        if (firstVendor === null) {
            firstVendor = vendor;
        } else if (vendor !== firstVendor) {
            alert('Error: Vendor bills yang dipilih harus dari vendor yang sama!\n\nVendor pertama: ' + firstVendor + '\nVendor berbeda: ' + vendor);
            return;
        }
    }
    
    
    checkboxes.forEach(checkbox => {
        const vendorBill = {
            id: checkbox.value,
            number: checkbox.getAttribute('data-number'),
            vendor: checkbox.getAttribute('data-vendor'),
            amount: parseFloat(checkbox.getAttribute('data-amount')),
            pph23: parseFloat(checkbox.getAttribute('data-pph23')),
            date: checkbox.getAttribute('data-date'),
            job: checkbox.getAttribute('data-job')
        };
        selectedVendorBills.push(vendorBill);
        totalAmount += vendorBill.amount;
    });
    
    // Auto-fill Reference Number (vendor bill numbers)
    const referenceNumbers = selectedVendorBills.map(vb => vb.number).join(', ');
    const referenceInput = document.querySelector('input[name="reference_number"]');
    if (referenceInput) {
        referenceInput.value = referenceNumbers;
    }
    
    // Auto-fill Description
    const vendorName = selectedVendorBills[0].vendor;
    const descriptionTextarea = document.querySelector('textarea[name="description"]');
    if (descriptionTextarea) {
        const billCount = selectedVendorBills.length;
        const billText = billCount === 1 ? 'vendor bill' : billCount + ' vendor bills';
        descriptionTextarea.value = `Pembayaran hutang ${vendorName} (${billText}: ${referenceNumbers})`;
    }
    
    // Auto-fill Recipient Name (vendor name)
    const recipientInput = document.querySelector('input[name="recipient_name"]');
    if (recipientInput) {
        recipientInput.value = vendorName;
    }
    
    // Update display
    displaySelectedVendorBills();
    
    // Update amount field (total_amount sudah net setelah potong PPh23)
    document.getElementById('amount_input').value = totalAmount;
    document.getElementById('amount_display').value = formatNumber(totalAmount.toFixed(0));
    
    updateTotalBank();

    // Close modal
    closeVendorBillModal();
}

function displaySelectedVendorBills() {
    const container = document.getElementById('selected_vendor_bills');
    if (!container) return; // Safety check
    
    container.innerHTML = '';
    
    if (selectedVendorBills.length === 0) {
        container.style.display = 'none';
        return;
    }

    // Show container
    container.style.display = 'block';

    // Create wrapper with scroll buttons
    const wrapper = document.createElement('div');
    wrapper.className = 'relative';
    
    // Create horizontal scroll container
    const scrollContainer = document.createElement('div');
    scrollContainer.id = 'vendor-bills-scroll';
    scrollContainer.className = 'flex gap-3 overflow-x-auto pb-3 scroll-smooth';
    scrollContainer.style.scrollbarWidth = 'thin';
    
    selectedVendorBills.forEach((vb, index) => {
        const hasPph23 = vb.pph23 && parseFloat(vb.pph23) > 0;
        
        const card = document.createElement('div');
        card.className = 'flex-shrink-0 w-80 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-lg p-3 transition-all hover:shadow-lg hover:scale-[1.02]';
        card.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                        <span class="font-bold text-blue-600 dark:text-blue-400 text-sm">${vb.number}</span>
                        ${hasPph23 ? '<span class="px-1.5 py-0.5 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs rounded-full font-medium" title="PPh23 sudah dipotong">PPh23</span>' : ''}
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-400 truncate">${vb.vendor}</div>
                </div>
                <button type="button" onclick="removeVendorBill(${index})" 
                        class="ml-2 p-1 rounded-full hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 hover:text-red-700 transition-colors flex-shrink-0"
                        title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="text-xs text-slate-500 dark:text-slate-400 mb-2 truncate" title="${vb.date} • JO: ${vb.job}">
                ${vb.date} • ${vb.job}
            </div>
            
            <div class="pt-2 border-t border-blue-200 dark:border-blue-800">
                <div class="text-right">
                    <div class="text-base font-bold text-blue-600 dark:text-blue-400">
                        Rp ${formatNumber(vb.amount.toFixed(0))}
                    </div>
                    ${hasPph23 ? '<div class="text-xs text-slate-500 dark:text-slate-400">Net</div>' : ''}
                </div>
            </div>
            
            <input type="hidden" name="vendor_bill_ids[]" value="${vb.id}">
        `;
        scrollContainer.appendChild(card);
    });
    
    wrapper.appendChild(scrollContainer);
    
    // Add scroll buttons if more than 3 items
    if (selectedVendorBills.length > 3) {
        // Left scroll button
        const leftBtn = document.createElement('button');
        leftBtn.type = 'button';
        leftBtn.className = 'absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-slate-800 shadow-lg rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors';
        leftBtn.innerHTML = `
            <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        `;
        leftBtn.onclick = () => {
            scrollContainer.scrollBy({ left: -320, behavior: 'smooth' });
        };
        
        // Right scroll button
        const rightBtn = document.createElement('button');
        rightBtn.type = 'button';
        rightBtn.className = 'absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-slate-800 shadow-lg rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors';
        rightBtn.innerHTML = `
            <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        `;
        rightBtn.onclick = () => {
            scrollContainer.scrollBy({ left: 320, behavior: 'smooth' });
        };
        
        wrapper.appendChild(leftBtn);
        wrapper.appendChild(rightBtn);
    }
    
    container.appendChild(wrapper);
    
    // Add summary
    const summary = document.createElement('div');
    summary.className = 'mt-3 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400';
    summary.innerHTML = `
        <span><span class="font-bold text-blue-600 dark:text-blue-400">${selectedVendorBills.length}</span> vendor bill${selectedVendorBills.length > 1 ? 's' : ''} dipilih</span>
        ${selectedVendorBills.length > 3 ? '<span class="flex items-center gap-1 text-slate-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg> Gunakan tombol panah atau scroll untuk navigasi</span>' : ''}
    `;
    container.appendChild(summary);
}

function removeVendorBill(index) {
    selectedVendorBills.splice(index, 1);
    displaySelectedVendorBills();
    
    // Recalculate total
    let totalAmount = 0;
    selectedVendorBills.forEach(vb => {
        totalAmount += vb.amount;
    });
    
    document.getElementById('amount_input').value = totalAmount;
    document.getElementById('amount_display').value = formatNumber(totalAmount.toFixed(0));
    updateTotalBank();
}

// ===== SEARCH & FILTER FUNCTIONS =====
let currentStatusFilter = 'all';

function searchVendorBills() {
    const searchInput = document.getElementById('vendorBillSearch');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const clearBtn = document.getElementById('clearSearch');
    const rows = document.querySelectorAll('.vendor-bill-row');
    let visibleCount = 0;
    
    // Show/hide clear button
    if (searchTerm) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search-text');
        const status = row.getAttribute('data-status');
        
        // Check search match
        const matchesSearch = !searchTerm || searchText.includes(searchTerm);
        
        // Check status filter
        const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;
        
        // Show/hide row
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateSearchCount(visibleCount);
    
    // Update status checkbox "Select All" setelah filter
    updateSelectedVendorBills();
}

function clearVendorBillSearch() {
    document.getElementById('vendorBillSearch').value = '';
    document.getElementById('clearSearch').classList.add('hidden');
    searchVendorBills();
}

function filterByStatus(status) {
    currentStatusFilter = status;
    
    // Update button styles
    document.querySelectorAll('.filter-status-btn').forEach(btn => {
        if (btn.getAttribute('data-status') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    searchVendorBills();
}

function updateSearchCount(count) {
    document.getElementById('searchResultCount').textContent = count;
}

// ===== INVOICE MODAL FUNCTIONS =====
let selectedInvoices = [];
let currentInvoiceStatusFilter = 'all';

function openInvoiceModal() {
    document.getElementById('invoiceModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeInvoiceModal() {
    document.getElementById('invoiceModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function toggleSelectAllInvoices() {
    const selectAll = document.getElementById('selectAllInvoices');
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    
    checkboxes.forEach(checkbox => {
        // Hanya checklist checkbox yang visible (tidak di-hide) dan tidak disabled
        const row = checkbox.closest('tr');
        const isVisible = row.style.display !== 'none';
        const isEnabled = !checkbox.disabled;
        
        if (isVisible && isEnabled) {
            checkbox.checked = selectAll.checked;
        }
    });
    
    updateSelectedInvoices();
}

function updateSelectedInvoices() {
    const checkboxes = document.querySelectorAll('.invoice-checkbox:checked');
    const allCheckboxes = document.querySelectorAll('.invoice-checkbox');
    const count = checkboxes.length;
    let total = 0;
    let selectedCustomer = null;
    
    // Get customer dari checkbox pertama yang dipilih
    if (checkboxes.length > 0) {
        selectedCustomer = checkboxes[0].getAttribute('data-customer');
    }
    
    // Update checkbox states based on customer selection
    allCheckboxes.forEach(checkbox => {
        const checkboxCustomer = checkbox.getAttribute('data-customer');
        const row = checkbox.closest('tr');
        const isPaid = row.getAttribute('data-status') === 'paid';
        
        // Skip if already paid (permanently disabled)
        if (isPaid) {
            return;
        }
        
        if (selectedCustomer && checkboxCustomer !== selectedCustomer && !checkbox.checked) {
            // Disable checkbox jika customer berbeda
            checkbox.disabled = true;
            row.classList.add('opacity-50', 'cursor-not-allowed');
            row.style.pointerEvents = 'none';
        } else {
            // Enable checkbox jika customer sama atau tidak ada yang dipilih
            checkbox.disabled = false;
            row.classList.remove('opacity-50', 'cursor-not-allowed');
            row.style.pointerEvents = 'auto';
        }
    });
    
    // Calculate total from CHECKED checkboxes only
    checkboxes.forEach(checkbox => {
        total += parseFloat(checkbox.getAttribute('data-amount'));
    });
    
    document.getElementById('selectedInvoiceCount').textContent = count;
    document.getElementById('selectedInvoiceTotal').textContent = 'Rp ' + formatNumber(total.toFixed(0));
    
    // Update info customer yang dipilih
    const customerInfo = document.getElementById('selectedCustomerInfo');
    if (customerInfo) {
        if (selectedCustomer) {
            customerInfo.textContent = 'Customer: ' + selectedCustomer;
            customerInfo.classList.remove('hidden');
        } else {
            customerInfo.classList.add('hidden');
        }
    }
    
    // Update status checkbox "Select All"
    const selectAllCheckbox = document.getElementById('selectAllInvoices');
    const visibleEnabledCheckboxes = Array.from(allCheckboxes).filter(cb => {
        const row = cb.closest('tr');
        return row.style.display !== 'none' && !cb.disabled;
    });
    
    const allVisibleChecked = visibleEnabledCheckboxes.length > 0 && 
                              visibleEnabledCheckboxes.every(cb => cb.checked);
    
    selectAllCheckbox.checked = allVisibleChecked;
}

function confirmInvoiceSelection() {
    const checkboxes = document.querySelectorAll('.invoice-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Silakan pilih minimal 1 invoice');
        return;
    }
    
    selectedInvoices = [];
    let totalAmount = 0;
    let totalPPh23 = 0;
    let firstCustomer = null;
    
    // Validasi: semua customer harus sama
    for (let checkbox of checkboxes) {
        const customer = checkbox.getAttribute('data-customer');
        
        if (firstCustomer === null) {
            firstCustomer = customer;
        } else if (customer !== firstCustomer) {
            alert('Error: Invoices yang dipilih harus dari customer yang sama!\\n\\nCustomer pertama: ' + firstCustomer + '\\nCustomer berbeda: ' + customer);
            return;
        }
    }
    
    checkboxes.forEach(checkbox => {
        const pph23 = parseFloat(checkbox.getAttribute('data-pph23')) || 0;
        
        const invoice = {
            id: checkbox.value,
            number: checkbox.getAttribute('data-number'),
            customer: checkbox.getAttribute('data-customer'),
            amount: parseFloat(checkbox.getAttribute('data-amount')),
            pph23: pph23,
            date: checkbox.getAttribute('data-date'),
            job: checkbox.getAttribute('data-job')
        };
        selectedInvoices.push(invoice);
        totalAmount += invoice.amount;
        totalPPh23 += pph23;
    });
    
    // Auto-fill Reference Number (invoice numbers)
    const referenceNumbers = selectedInvoices.map(inv => inv.number).join(', ');
    const referenceInput = document.querySelector('input[name="reference_number"]');
    if (referenceInput) {
        referenceInput.value = referenceNumbers;
    }
    
    // Auto-fill Description
    const customerName = selectedInvoices[0].customer;
    const descriptionTextarea = document.querySelector('textarea[name="description"]');
    if (descriptionTextarea) {
        const invCount = selectedInvoices.length;
        const invText = invCount === 1 ? 'invoice' : invCount + ' invoices';
        descriptionTextarea.value = `Pembayaran piutang ${customerName} (${invText}: ${referenceNumbers})`;
    }
    
    // Auto-fill Recipient Name (customer name)
    const recipientInput = document.querySelector('input[name="recipient_name"]');
    if (recipientInput) {
        recipientInput.value = customerName;
    }
    
    // Auto-fill PPh23 from invoice (already calculated)
    if (totalPPh23 > 0) {
        document.getElementById('pph23_display').value = formatNumber(totalPPh23.toFixed(0));
        document.getElementById('pph23_input').value = totalPPh23;
        
        // Show info notification
        showPPh23InfoNotification(totalPPh23);
        
        // Set flag
        isPPh23FromInvoice = true;
    } else {
        isPPh23FromInvoice = false;
    }
    
    // Update display
    displaySelectedInvoices();
    
    // Update amount field
    document.getElementById('amount_input').value = totalAmount;
    document.getElementById('amount_display').value = formatNumber(totalAmount.toFixed(0));
    
    updateTotalBank();

    // Close modal
    closeInvoiceModal();
}

function displaySelectedInvoices() {
    const container = document.getElementById('selected_invoices');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (selectedInvoices.length === 0) {
        container.style.display = 'none';
        return;
    }

    // Show container
    container.style.display = 'block';

    // Create wrapper with scroll buttons
    const wrapper = document.createElement('div');
    wrapper.className = 'relative';
    
    // Create horizontal scroll container
    const scrollContainer = document.createElement('div');
    scrollContainer.id = 'invoices-scroll';
    scrollContainer.className = 'flex gap-3 overflow-x-auto pb-3 scroll-smooth';
    scrollContainer.style.scrollbarWidth = 'thin';
    
    selectedInvoices.forEach((inv, index) => {
        const hasPph23 = inv.pph23 && parseFloat(inv.pph23) > 0;
        
        const card = document.createElement('div');
        card.className = 'flex-shrink-0 w-80 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-2 border-green-200 dark:border-green-800 rounded-lg p-3 transition-all hover:shadow-lg hover:scale-[1.02]';
        card.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                        <span class="font-bold text-green-600 dark:text-green-400 text-sm">${inv.number}</span>
                        ${hasPph23 ? '<span class="px-1.5 py-0.5 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs rounded-full font-medium" title="PPh23">PPh23</span>' : ''}
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-400 truncate">${inv.customer}</div>
                </div>
                <button type="button" onclick="removeInvoice(${index})" 
                        class="ml-2 p-1 rounded-full hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 hover:text-red-700 transition-colors flex-shrink-0"
                        title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="text-xs text-slate-500 dark:text-slate-400 mb-2 truncate" title="${inv.date} • JO: ${inv.job}">
                ${inv.date} • ${inv.job}
            </div>
            
            <div class="pt-2 border-t border-green-200 dark:border-green-800">
                <div class="text-right">
                    <div class="text-base font-bold text-green-600 dark:text-green-400">
                        Rp ${formatNumber(inv.amount.toFixed(0))}
                    </div>
                    ${hasPph23 ? '<div class="text-xs text-slate-500 dark:text-slate-400">Outstanding</div>' : ''}
                </div>
            </div>
            
            <input type="hidden" name="invoice_ids[]" value="${inv.id}">
        `;
        scrollContainer.appendChild(card);
    });
    
    wrapper.appendChild(scrollContainer);
    
    // Add scroll buttons if more than 3 items
    if (selectedInvoices.length > 3) {
        // Left scroll button
        const leftBtn = document.createElement('button');
        leftBtn.type = 'button';
        leftBtn.className = 'absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-slate-800 shadow-lg rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors';
        leftBtn.innerHTML = `
            <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        `;
        leftBtn.onclick = () => {
            scrollContainer.scrollBy({ left: -320, behavior: 'smooth' });
        };
        
        // Right scroll button
        const rightBtn = document.createElement('button');
        rightBtn.type = 'button';
        rightBtn.className = 'absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-slate-800 shadow-lg rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors';
        rightBtn.innerHTML = `
            <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        `;
        rightBtn.onclick = () => {
            scrollContainer.scrollBy({ left: 320, behavior: 'smooth' });
        };
        
        wrapper.appendChild(leftBtn);
        wrapper.appendChild(rightBtn);
    }
    
    container.appendChild(wrapper);
    
    // Add summary
    const summary = document.createElement('div');
    summary.className = 'mt-3 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400';
    summary.innerHTML = `
        <span><span class="font-bold text-green-600 dark:text-green-400">${selectedInvoices.length}</span> invoice${selectedInvoices.length > 1 ? 's' : ''} dipilih</span>
        ${selectedInvoices.length > 3 ? '<span class="flex items-center gap-1 text-slate-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg> Gunakan tombol panah atau scroll untuk navigasi</span>' : ''}
    `;
    container.appendChild(summary);
}

function removeInvoice(index) {
    selectedInvoices.splice(index, 1);
    displaySelectedInvoices();
    
    // Recalculate total
    let totalAmount = 0;
    selectedInvoices.forEach(inv => {
        totalAmount += inv.amount;
    });
    
    document.getElementById('amount_input').value = totalAmount;
    document.getElementById('amount_display').value = formatNumber(totalAmount.toFixed(0));
    updateTotalBank();
}

// ===== INVOICE SEARCH & FILTER FUNCTIONS =====
function searchInvoices() {
    const searchInput = document.getElementById('invoiceSearch');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const clearBtn = document.getElementById('clearInvoiceSearch');
    const rows = document.querySelectorAll('.invoice-row');
    let visibleCount = 0;
    
    // Show/hide clear button
    if (searchTerm) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search-text');
        const status = row.getAttribute('data-status');
        
        // Check search match
        const matchesSearch = !searchTerm || searchText.includes(searchTerm);
        
        // Check status filter
        const matchesStatus = currentInvoiceStatusFilter === 'all' || status === currentInvoiceStatusFilter;
        
        // Show/hide row
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateInvoiceSearchCount(visibleCount);
    
    // Update status checkbox "Select All" setelah filter
    updateSelectedInvoices();
}

function clearInvoiceSearch() {
    document.getElementById('invoiceSearch').value = '';
    document.getElementById('clearInvoiceSearch').classList.add('hidden');
    searchInvoices();
}

function filterInvoiceByStatus(status) {
    currentInvoiceStatusFilter = status;
    
    // Update button styles
    document.querySelectorAll('.filter-status-btn').forEach(btn => {
        if (btn.getAttribute('data-status') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    searchInvoices();
}

function updateInvoiceSearchCount(count) {
    document.getElementById('invoiceSearchResultCount').textContent = count;
}

// ===== PPH23 AUTO-CALCULATE FUNCTIONS =====
let autoPPh23Enabled = false;

function autoCalculatePPh23() {
    // For manual entry - just calculate 2% from amount
    const amountInput = document.getElementById('amount_input');
    const amount = parseFloat(amountInput.value) || 0;
    
    if (amount <= 0) {
        alert('Silakan isi nominal terlebih dahulu');
        return;
    }
    
    
    
    // Check if PPh23 is currently from invoice
    if (isPPh23FromInvoice) {
        showConfirmationModal(
            'Hitung Ulang PPh 23?',
            'PPh 23 saat ini <b>sudah sesuai</b> dengan data Invoice terpilih.<br><br>Apakah Anda yakin ingin menghitung ulang menjadi 2% dari Nominal? Ini akan <b>menimpa</b> nilai PPh 23 dari Invoice.',
            function() {
                // If confirmed logic
                isPPh23FromInvoice = false;
                performCalculatePPh23(amount);
            }
        );
        return;
    }

    performCalculatePPh23(amount);
}

function performCalculatePPh23(amount) {
    // Calculate 2% of amount (for manual entry)
    const pph23 = amount * 0.02;
    
    document.getElementById('pph23_display').value = formatNumber(pph23.toFixed(0));
    document.getElementById('pph23_input').value = pph23;
    
    // Show notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 max-w-sm animate-fade-in-down';
    notification.innerHTML = `
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <div>
                <div class="font-semibold">PPh23 Dihitung (Manual)</div>
                <div class="text-sm mt-1 opacity-90">
                    2% × Rp ${formatNumber(amount.toFixed(0))} = Rp ${formatNumber(pph23.toFixed(0))}
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        notification.style.transition = 'all 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
    
    updateTotalBank();
}

// ===== CUSTOM MODAL HELPER FUNCTIONS =====
let onConfirmCallback = null;

function showConfirmationModal(title, message, callback) {
    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmMessage').innerHTML = message;
    
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('hidden');
    
    // Animate in
    const content = modal.querySelector('div');
    content.classList.remove('scale-95', 'opacity-0');
    content.classList.add('scale-100', 'opacity-100');
    
    onConfirmCallback = callback;
    document.body.style.overflow = 'hidden';
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    
    // Animate out
    const content = modal.querySelector('div');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        onConfirmCallback = null;
    }, 200); // Wait for animation
}

// Setup confirm button listener on load
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('confirmActionBtn');
    if (btn) {
        btn.addEventListener('click', function() {
            if (onConfirmCallback) {
                onConfirmCallback();
            }
            closeConfirmationModal();
        });
    }
});


function clearPPh23() {
    document.getElementById('pph23_display').value = '';
    document.getElementById('pph23_input').value = '0';
    isPPh23FromInvoice = false;
    updateTotalBank();
}

function toggleAutoPPh23() {
    const checkbox = document.getElementById('auto_pph23');
    autoPPh23Enabled = checkbox.checked;
    
    if (autoPPh23Enabled) {
        // Calculate immediately if amount is already filled
        const amount = parseFloat(document.getElementById('amount_input').value) || 0;
        if (amount > 0) {
            autoCalculatePPh23();
        }
    }
}

function showPPh23InfoNotification(pph23Amount) {
    // Show info that PPh23 is taken from invoice
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 max-w-sm';
    notification.innerHTML = `
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <div class="font-semibold">PPh23 dari Invoice</div>
                <div class="text-sm mt-1 opacity-90">
                    Total PPh23: Rp ${formatNumber(pph23Amount.toFixed(0))}<br>
                    <span class="text-xs">(Sudah dihitung saat create invoice)</span>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// ===== TOTAL BANK CALCULATION =====
function updateTotalBank() {
    const jenis = document.getElementById('jenis').value;
    const amount = parseFloat(document.getElementById('amount_input').value) || 0;
    const pph23 = parseFloat(document.getElementById('pph23_input').value) || 0;
    const adminFee = parseFloat(document.getElementById('admin_fee_input').value) || 0;
    
    let totalBank = 0;
    let formula = '';
    let label = '';
    let hint = '';
    
    if (jenis === 'cash_in') {
        // Customer Payment / Other In: Nominal - PPh23 - Admin Fee
        // Nominal = Total Invoice / Income
        // PPh23 = Tax withheld by customer (we receive less)
        // Admin Fee = Bank charge (we receive less)
        // Total Bank = Nominal - PPh23 - Admin Fee
        
        totalBank = amount - pph23 - adminFee;
        
        if (adminFee > 0) {
            formula = `Rp ${formatNumber(amount.toFixed(0))} - Rp ${formatNumber(pph23.toFixed(0))} - Rp ${formatNumber(adminFee.toFixed(0))} (admin)`;
        } else {
            formula = `Rp ${formatNumber(amount.toFixed(0))} - Rp ${formatNumber(pph23.toFixed(0))}`;
        }
        
        label = 'Total Diterima';
        hint = 'Nominal - PPh23 - Admin';
    } else if (jenis === 'cash_out') {
        // Vendor Payment / Expense / Other Out: Nominal - PPh23 + Admin Fee
        // Nominal = Total Bill / Expense
        // PPh23 = Tax withheld by us (we pay less to vendor)
        // Admin Fee = Bank charge (we pay more to bank)
        // Total Bank = (Nominal - PPh23) + Admin Fee
        
        totalBank = amount - pph23 + adminFee;
        
        if (adminFee > 0) {
            formula = `Rp ${formatNumber(amount.toFixed(0))} - Rp ${formatNumber(pph23.toFixed(0))} + Rp ${formatNumber(adminFee.toFixed(0))} (admin)`;
        } else {
            formula = `Rp ${formatNumber(amount.toFixed(0))} - Rp ${formatNumber(pph23.toFixed(0))}`;
        }
        
        label = 'Total Dibayar';
        hint = 'Nominal - PPh23 + Admin';
    } else {
        // Fallback
        totalBank = amount + adminFee;
        
        if (adminFee > 0) {
            formula = `Rp ${formatNumber(amount.toFixed(0))} + Rp ${formatNumber(adminFee.toFixed(0))} (admin)`;
        } else {
            formula = `Rp ${formatNumber(amount.toFixed(0))}`;
        }
        
        label = 'Total Transaksi';
        hint = 'Nominal + Admin';
    }
    
    // Update display
    document.getElementById('total_bank_display').value = 'Rp ' + formatNumber(totalBank.toFixed(0));
    document.getElementById('total_bank_label').textContent = label;
    document.getElementById('total_bank_hint').textContent = hint;
}

function showPPh23Notification(pph23Amount, dppAmount) {
    // Legacy function - not used anymore
    showPPh23InfoNotification(pph23Amount);
}

// ===== DRIVER ADVANCE MODAL FUNCTIONS =====
let selectedDriverAdvances = [];

function openDriverAdvanceModal() {
    document.getElementById('driverAdvanceModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDriverAdvanceModal() {
    document.getElementById('driverAdvanceModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function toggleSelectAllDriverAdvances() {
    const selectAll = document.getElementById('selectAllDriverAdvances');
    const checkboxes = document.querySelectorAll('.driver-advance-checkbox');
    
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const isVisible = row.style.display !== 'none';
        
        if (isVisible) {
            checkbox.checked = selectAll.checked;
        }
    });
    
    updateSelectedDriverAdvances();
}

function updateSelectedDriverAdvances() {
    const checkboxes = document.querySelectorAll('.driver-advance-checkbox:checked');
    const allCheckboxes = document.querySelectorAll('.driver-advance-checkbox');
    const count = checkboxes.length;
    let total = 0;
    let selectedDriver = null;
    
    // Get driver from first checked checkbox
    if (checkboxes.length > 0) {
        selectedDriver = checkboxes[0].getAttribute('data-driver');
    }
    
    // Update checkbox states based on driver selection
    allCheckboxes.forEach(checkbox => {
        const checkboxDriver = checkbox.getAttribute('data-driver');
        const row = checkbox.closest('tr');
        
        if (selectedDriver && checkboxDriver !== selectedDriver && !checkbox.checked) {
            checkbox.disabled = true;
            row.classList.add('opacity-50', 'cursor-not-allowed');
            row.style.pointerEvents = 'none';
        } else {
            checkbox.disabled = false;
            row.classList.remove('opacity-50', 'cursor-not-allowed');
            row.style.pointerEvents = 'auto';
        }
    });
    
    // Calculate total from CHECKED checkboxes only
    checkboxes.forEach(checkbox => {
        total += parseFloat(checkbox.getAttribute('data-amount'));
    });
    
    document.getElementById('selectedDriverAdvanceCount').textContent = count;
    document.getElementById('selectedDriverAdvanceTotal').textContent = 'Rp ' + formatNumber(total.toFixed(0));
    
    // Update driver info
    const driverInfo = document.getElementById('selectedDriverInfo');
    if (driverInfo) {
        if (selectedDriver) {
            driverInfo.textContent = 'Driver: ' + selectedDriver;
            driverInfo.classList.remove('hidden');
        } else {
            driverInfo.classList.add('hidden');
        }
    }
}

function confirmDriverAdvanceSelection() {
    const checkboxes = document.querySelectorAll('.driver-advance-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Please select at least one driver advance');
        return;
    }
    
    selectedDriverAdvances = [];
    let totalAmount = 0;
    let descriptions = [];
    
    checkboxes.forEach(checkbox => {
        const advance = {
            id: checkbox.value,
            number: checkbox.getAttribute('data-number'),
            driver: checkbox.getAttribute('data-driver'),
            amount: parseFloat(checkbox.getAttribute('data-amount')), // Net amount
            gross: parseFloat(checkbox.getAttribute('data-gross')),
            savings: parseFloat(checkbox.getAttribute('data-savings')),
            guarantee: parseFloat(checkbox.getAttribute('data-guarantee')),
            date: checkbox.getAttribute('data-date'),
            job: checkbox.getAttribute('data-job'),
            customer: checkbox.getAttribute('data-customer'),
            plate: checkbox.getAttribute('data-plate'),
            origin: checkbox.getAttribute('data-origin'),
            destination: checkbox.getAttribute('data-destination'),
            cargoQty: checkbox.getAttribute('data-cargo-qty'),
            cargoUnit: checkbox.getAttribute('data-cargo-unit'),
            cargoDesc: checkbox.getAttribute('data-cargo-desc'),
            paymentType: checkbox.getAttribute('data-payment-type')
        };
        selectedDriverAdvances.push(advance);
          totalAmount += advance.amount; // Use net amount
          
          // Generate description for this advance
          // Format: "Bayar uang jalan [Driver] [Nopol] order [Customer] muat [Qty Unit Cargo] [Origin]-[Destination] [Job Number]"
          // Tidak membedakan DP / pelunasan di teks agar tetap relevan untuk pembayaran penuh maupun sebagian.
          const desc = `Bayar uang jalan ${advance.driver} ${advance.plate} order ${advance.customer} muat ${advance.cargoQty} ${advance.cargoUnit} ${advance.cargoDesc} ${advance.origin}-${advance.destination} ${advance.job}`;
        descriptions.push(desc);
    });
    
    // Update amount field
    document.getElementById('amount_input').value = totalAmount;
    document.getElementById('amount_display').value = formatNumber(totalAmount.toFixed(0));
    
    // Update recipient name
    if (selectedDriverAdvances.length > 0) {
        document.getElementById('recipient_name').value = selectedDriverAdvances[0].driver;
    }
    
    // Auto-fill description
    const descriptionField = document.getElementById('description');
    if (descriptionField && descriptions.length > 0) {
        descriptionField.value = descriptions.join('; ');
    }
    
    // Display selected driver advances
    displaySelectedDriverAdvances();
    
    // Update total bank
    updateTotalBank();
    
    closeDriverAdvanceModal();
}

function displaySelectedDriverAdvances() {
    const container = document.getElementById('selected_driver_advances');
    
    if (selectedDriverAdvances.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    let html = `
        <div class="border border-slate-300 dark:border-slate-700 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-slate-900 dark:text-slate-100">Selected Driver Advances (${selectedDriverAdvances.length})</h4>
                <button type="button" onclick="clearDriverAdvanceSelection()" class="text-xs text-red-600 hover:text-red-700">Clear All</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400">Advance #</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400">Driver</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400">Job Order</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400">Gross</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400">Deductions</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400">Net Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
    `;
    
    selectedDriverAdvances.forEach(advance => {
        const hasDeductions = (advance.savings > 0 || advance.guarantee > 0);
        const totalDeductions = advance.savings + advance.guarantee;
        
        html += `
            <tr>
                <td class="px-3 py-2 text-slate-900 dark:text-slate-100">${advance.number}</td>
                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${advance.driver}</td>
                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${advance.date}</td>
                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${advance.job}</td>
                <td class="px-3 py-2 text-right font-mono text-slate-600 dark:text-slate-400">Rp ${formatNumber(advance.gross.toFixed(0))}</td>
                <td class="px-3 py-2 text-right">
                    ${hasDeductions ? `
                        <div class="font-mono text-red-500 text-xs">Rp ${formatNumber(totalDeductions.toFixed(0))}</div>
                        <div class="text-xs text-slate-500">
                            ${advance.savings > 0 ? `Savings: ${formatNumber(advance.savings.toFixed(0))}` : ''}
                            ${advance.savings > 0 && advance.guarantee > 0 ? '<br>' : ''}
                            ${advance.guarantee > 0 ? `Guarantee: ${formatNumber(advance.guarantee.toFixed(0))}` : ''}
                        </div>
                    ` : '<span class="text-xs text-slate-400">-</span>'}
                </td>
                <td class="px-3 py-2 text-right font-mono font-bold text-green-600 dark:text-green-400">Rp ${formatNumber(advance.amount.toFixed(0))}</td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                </table>
            </div>
    `;
    
    // Add hidden inputs for each selected driver advance ID
    selectedDriverAdvances.forEach(advance => {
        html += `<input type="hidden" name="driver_advance_ids[]" value="${advance.id}">`;
    });
    
    html += `
        </div>
    `;
    
    container.innerHTML = html;
    container.style.display = 'block';
}

function clearDriverAdvanceSelection() {
    selectedDriverAdvances = [];
    document.querySelectorAll('.driver-advance-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllDriverAdvances').checked = false;
    document.getElementById('selected_driver_advances').style.display = 'none';
    document.getElementById('amount_input').value = '';
    document.getElementById('amount_display').value = '';
    updateSelectedDriverAdvances();
    updateTotalBank();
}

function searchDriverAdvances() {
    const searchTerm = document.getElementById('driverAdvanceSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.driver-advance-row');
    const clearBtn = document.getElementById('clearDriverAdvanceSearch');
    let visibleCount = 0;
    
    // Show/hide clear button
    if (searchTerm) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search-text');
        
        if (!searchTerm || searchText.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('driverAdvanceSearchResultCount').textContent = visibleCount;
    updateSelectedDriverAdvances();
}

function clearDriverAdvanceSearch() {
    document.getElementById('driverAdvanceSearch').value = '';
    document.getElementById('clearDriverAdvanceSearch').classList.add('hidden');
    searchDriverAdvances();
}

document.addEventListener('DOMContentLoaded', function() {
    // Update total bank when jenis changes
    const jenisSelect = document.getElementById('jenis');
    if (jenisSelect) {
        jenisSelect.addEventListener('change', function() {
            updateTotalBank();
        });
    }
    
    // Initial calculation
    updateTotalBank();
});
</script>
@endsection
