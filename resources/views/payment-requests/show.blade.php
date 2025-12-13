@extends('layouts.app', ['title' => 'Payment Request Detail'])

@section('content')
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $request->request_number }}</div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Vendor payment request detail</p>
            </div>
            @if($request->status === 'pending' && Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
            <div class="flex items-center gap-2">
                <button
                    onclick="document.getElementById('reject_modal').classList.remove('hidden')"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reject
                </button>
                <form method="POST" action="{{ route('payment-requests.approve', $request) }}" class="flex-1 sm:flex-none">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Approve this payment request?')"
                        class="w-full px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approve
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Request Information">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Request Number</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $request->request_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Request Date</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $request->request_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Requested By</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $request->requestedBy->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Status</div>
                        <x-badge :variant="match($request->status) {
                            'pending' => 'default',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'paid' => 'success',
                            default => 'default'
                        }">{{ strtoupper($request->status) }}</x-badge>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Request Amount</div>
                        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($request->amount, 0, ',', '.') }}</div>
                    </div>
                    @if($request->vendorBankAccount)
                    <div class="col-span-2">
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Transfer Destination Account</div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $request->vendorBankAccount->bank_name }}</div>
                                    <div class="text-sm text-slate-600 dark:text-slate-400 font-mono">{{ $request->vendorBankAccount->account_number }}</div>
                                    <div class="text-sm text-slate-600 dark:text-slate-400">a.n. {{ $request->vendorBankAccount->account_holder_name }}</div>
                                    @if($request->vendorBankAccount->branch)
                                    <div class="text-xs text-slate-500 dark:text-slate-500 mt-1">Branch: {{ $request->vendorBankAccount->branch }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-span-2">
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Notes</div>
                        <div class="text-sm text-slate-900 dark:text-slate-100">{{ $request->notes ?: $request->auto_description }}</div>
                    </div>
                </div>

                @if($request->status === 'approved')
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex items-start gap-3 text-sm text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <div class="font-medium">Approved by {{ $request->approvedBy->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $request->approved_at ? $request->approved_at->format('d M Y H:i') : '-' }}</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($request->status === 'rejected')
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex items-start gap-3 text-sm text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <div class="font-medium">Rejected by {{ $request->approvedBy->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ $request->approved_at ? $request->approved_at->format('d M Y H:i') : '-' }}</div>
                            @if($request->rejection_reason)
                            <div class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                                <strong>Reason:</strong> {{ $request->rejection_reason }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </x-card>

            {{-- Vendor Bill / Driver Advance / Manual Payment Info --}}
            @if($request->payment_type === 'vendor_bill' && $request->vendorBill)
            <x-card title="Related Vendor Bill">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Number</div>
                        <a href="{{ route('vendor-bills.show', $request->vendorBill) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $request->vendorBill->vendor_bill_number }}
                        </a>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Vendor</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $request->vendorBill->vendor->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Bill</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">Rp {{ number_format($request->vendorBill->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Status</div>
                        <x-badge :variant="match($request->vendorBill->status) {
                            'draft' => 'default',
                            'received' => 'warning',
                            'partially_paid' => 'warning',
                            'paid' => 'success',
                            'cancelled' => 'danger',
                            default => 'default'
                        }" class="text-xs">{{ strtoupper($request->vendorBill->status) }}</x-badge>
                    </div>
                </div>
            </x-card>
            @elseif($request->driverAdvance)
            {{-- Driver Advance Info --}}
            <x-card title="Related Driver Advance">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Number</div>
                        <a href="{{ route('driver-advances.show', $request->driverAdvance) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $request->driverAdvance->advance_number }}
                        </a>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Driver</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $request->driverAdvance->driver->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Advance Amount</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">Rp {{ number_format($request->driverAdvance->amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Status</div>
                        <x-badge :variant="$request->driverAdvance->status === 'pending' ? 'warning' : ($request->driverAdvance->status === 'settled' ? 'success' : 'info')" class="text-xs">
                            {{ strtoupper($request->driverAdvance->status) }}
                        </x-badge>
                    </div>
                </div>
            </x-card>
            @else
            {{-- Manual Payment Request Info --}}
            @php
                $manualPayee = $manualBank = $manualAccount = $manualHolder = null;
                if ($request->notes) {
                    foreach (preg_split("/\r\n|\n|\r/", $request->notes) as $line) {
                        if (strpos($line, 'Manual payee info') !== false) {
                            if (preg_match('/Payee:\s*([^|]+)/', $line, $m)) {
                                $manualPayee = trim($m[1]);
                            }
                            if (preg_match('/Bank:\s*([^|]+)/', $line, $m)) {
                                $manualBank = trim($m[1]);
                            }
                            if (preg_match('/No Rek:\s*([^|]+)/', $line, $m)) {
                                $manualAccount = trim($m[1]);
                            }
                            if (preg_match('/a\.n:\s*([^|]+)/', $line, $m)) {
                                $manualHolder = trim($m[1]);
                            }
                            break;
                        }
                    }
                }
            @endphp
            <x-card title="Payment Information">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Request Type</div>
                            <x-badge variant="warning" class="text-xs">{{ strtoupper(str_replace('_', ' ', $request->payment_type)) }}</x-badge>
                        </div>
                        @if($request->description)
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Description</div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $request->description }}</div>
                        </div>
                        @endif

                        @if($request->vendor)
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Linked Vendor (optional)</div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $request->vendor->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $request->vendor->phone }}</div>
                        </div>
                        @endif
                    </div>

                    @if($manualPayee || $manualBank || $manualAccount || $manualHolder)
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Transfer To</div>
                        <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30 p-3 space-y-1">
                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                {{ $manualPayee ?? '-' }}
                            </div>
                            <div class="text-xs text-slate-600 dark:text-slate-300">
                                @if($manualBank)
                                    {{ $manualBank }}
                                @endif
                                @if($manualAccount)
                                    {{ $manualBank ? ' · ' : '' }}{{ $manualAccount }}
                                @endif
                            </div>
                            @if($manualHolder)
                            <div class="text-xs text-slate-600 dark:text-slate-300">
                                a.n. {{ $manualHolder }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div>
            <x-card title="Actions">
                <div class="space-y-2">
                    @if($request->status === 'approved')
                    <x-button :href="route('cash-banks.create', ['payment_request_id' => $request->id])" class="w-full justify-center bg-indigo-600 hover:bg-indigo-700 text-white">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Pay Request
                    </x-button>
                    @endif
                    <x-button :href="url()->previous()" variant="outline" class="w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </x-button>

                    @if($request->payment_type === 'vendor_bill' && $request->vendorBill)
                    <x-button :href="route('vendor-bills.show', $request->vendorBill)" variant="outline" class="w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Vendor Bill
                    </x-button>
                    @endif

                    @if($request->payment_type === 'trucking' && $request->driverAdvance)
                    <x-button :href="route('driver-advances.show', $request->driverAdvance)" variant="outline" class="w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        View Driver Advance
                    </x-button>
                    @endif

                    @if($request->payment_type === 'manual' && $request->vendor)
                    <x-button :href="route('vendors.edit', $request->vendor)" variant="outline" class="w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        View Vendor
                    </x-button>
                    @endif

                    @if($request->status === 'pending' && Auth::check() && (Auth::user()->id === $request->requested_by || (Auth::user()->role ?? 'admin') === 'super_admin'))
                    <form method="POST" action="{{ route('payment-requests.destroy', $request) }}" onsubmit="return confirm('Are you sure you want to delete this request?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Request
                        </button>
                    </form>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="reject_modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Reject Request</h3>
                    <button onclick="document.getElementById('reject_modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('payment-requests.reject', $request) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                            <textarea
                                name="rejection_reason"
                                rows="4"
                                required
                                placeholder="Explain the rejection reason"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            ></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3 pt-4">
                            <button type="button" onclick="document.getElementById('reject_modal').classList.add('hidden')" class="w-full sm:flex-1 px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="w-full sm:flex-1 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-medium transition-colors">
                                Reject Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Close modal on outside click
    document.getElementById('reject_modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
    </script>
@endsection
