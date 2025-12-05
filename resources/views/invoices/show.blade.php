@extends('layouts.app', ['title' => 'Detail Invoice'])

@section('content')
    @php
        $user = auth()->user();
        $canSubmitInvoice = $user?->hasPermission('invoices.submit');
        $canApproveInvoice = $user?->hasPermission('invoices.approve');
        $canManageStatus = $user?->hasPermission('invoices.manage_status');
        $canCancelInvoice = $user?->hasPermission('invoices.cancel');
        $canUpdateInvoice = $user?->hasPermission('invoices.update');
        $needsJournal = is_null($invoice->journal_id);
    @endphp
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors" title="Kembali ke List Invoice">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <div class="text-xl font-semibold">{{ $invoice->invoice_number }}</div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $invoice->customer->name ?? '-' }}</p>
                @php
                    $jobNumbers = $invoice->items->pluck('jobOrder.job_number')->filter()->unique()->values();
                @endphp
                <div class="flex flex-wrap gap-1 mt-1">
                    @forelse($jobNumbers as $jn)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200 text-[11px] font-medium" title="Job Order">
                            {{ $jn }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400">Tidak ada JO terhubung</span>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-slate-300 transition-colors" title="Print / PDF">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            </a>

            @if($invoice->status !== 'cancelled')
                <a href="{{ route('cash-banks.create', ['sumber'=>'customer_payment','invoice_id'=>$invoice->id,'amount'=>$invoice->total_amount]) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors" title="Terima Pembayaran">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </a>
            @endif

            {{-- Submit for Approval Button --}}
            @if($invoice->canBeSubmittedForApproval() && $canSubmitInvoice)
                <form method="post" action="{{ route('invoices.submit-approval', $invoice) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors" title="Ajukan untuk Approval">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                </form>
            @endif

            {{-- Approve Button --}}
            @if($invoice->canBeApproved() && $canApproveInvoice)
                <form method="post" action="{{ route('invoices.approve', $invoice) }}" onsubmit="return confirm('Apakah Anda yakin ingin meng-approve invoice ini?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors" title="Approve Invoice">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </button>
                </form>
            @endif

            {{-- Reject Button --}}
            @if($invoice->canBeApproved() && $canApproveInvoice)
                <button onclick="document.getElementById('rejectModal').classList.remove('hidden')" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors" title="Reject Invoice">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            @endif

            @if($canManageStatus && $needsJournal && $invoice->status !== 'cancelled')
                <form method="post" action="{{ route('invoices.mark-as-sent', $invoice) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors" title="Buat jurnal untuk invoice ini">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    </button>
                </form>
            @endif

            @if($invoice->status === 'sent' && $invoice->paid_amount == 0 && $canManageStatus)
                <form method="post" action="{{ route('invoices.revert-to-draft', $invoice) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan invoice ke Draft? Jurnal akuntansi akan DIHAPUS.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 transition-colors" title="Revert to Draft">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </button>
                </form>
            @endif

            @if($invoice->canBeCancelled() && $canCancelInvoice)
                <form method="post" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan invoice ini?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors" title="Cancel Invoice">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </form>
            @endif

            {{-- Edit/Revise Button --}}
            @if($invoice->canBeEdited() && $canUpdateInvoice)
                {{-- Normal Edit for Draft --}}
                <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors" title="Edit Invoice">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </a>
            @elseif($invoice->canBeRevised() && $canUpdateInvoice)
                {{-- Revise Button for Approved/Rejected --}}
                <a href="{{ route('invoices.revise', $invoice) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-600 text-white hover:bg-amber-700 transition-colors" title="Revise Invoice">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Revision Info --}}
    @if($invoice->isRevision())
        <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <div class="flex-1">
                    <strong>Revision {{ $invoice->revision_number }}</strong>
                    @if($invoice->revised_at)
                        <span class="text-sm">- Revised on {{ $invoice->revised_at->format('d M Y H:i') }} by {{ $invoice->revisedBy->name ?? '-' }}</span>
                    @endif
                </div>
            </div>
            @if($invoice->revision_reason)
                <p class="text-sm mt-2 text-amber-700 dark:text-amber-300">
                    <strong>Reason:</strong> {{ $invoice->revision_reason }}
                </p>
            @endif
            @if($invoice->originalInvoice)
                <a href="{{ route('invoices.show', $invoice->originalInvoice) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block">
                    → View Original Invoice ({{ $invoice->originalInvoice->invoice_number }})
                </a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Ringkasan">
            <div class="space-y-1 text-sm">
                <div>Tanggal: {{ $invoice->invoice_date->format('d M Y') }}</div>
                <div>Jatuh Tempo: {{ optional($invoice->due_date)->format('d M Y') ?: '-' }}</div>
                <div>Status: <x-badge>{{ ucfirst(str_replace('_',' ', $invoice->status)) }}</x-badge></div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <div>Subtotal: <b>{{ number_format($invoice->subtotal, 2, ',', '.') }}</b></div>
                    @if($invoice->tax_amount > 0)
                        <div>PPN: <b>{{ number_format($invoice->tax_amount, 2, ',', '.') }}</b></div>
                    @endif
                    @if($invoice->discount_amount > 0)
                        <div>Discount: <b class="text-red-600">-{{ number_format($invoice->discount_amount, 2, ',', '.') }}</b></div>
                    @endif
                    <div class="font-bold text-indigo-600 dark:text-indigo-400">Total: {{ number_format($invoice->total_amount, 2, ',', '.') }}</div>
                    @if($invoice->show_pph23)
                        <div class="text-amber-600 dark:text-amber-400 text-xs mt-1">PPh 23: -{{ number_format($invoice->pph23_amount, 2, ',', '.') }}</div>
                        <div class="font-semibold text-emerald-600 dark:text-emerald-400">Net Payable: {{ number_format($invoice->total_amount - $invoice->pph23_amount, 2, ',', '.') }}</div>
                    @endif
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <div class="font-semibold mb-1">Status Approval</div>
                    @if($invoice->approval_status === 'draft')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">Draft</span>
                    @elseif($invoice->approval_status === 'pending_approval')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Menunggu Approval</span>
                    @elseif($invoice->approval_status === 'approved')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Approved</span>
                        <div class="text-xs text-slate-500 mt-1">Oleh: {{ $invoice->approvedBy->name ?? '-' }}</div>
                        <div class="text-xs text-slate-500">Pada: {{ $invoice->approved_at?->format('d M Y H:i') ?? '-' }}</div>
                    @elseif($invoice->approval_status === 'rejected')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Rejected</span>
                        <div class="text-xs text-slate-500 mt-1">Oleh: {{ $invoice->rejectedBy->name ?? '-' }}</div>
                        <div class="text-xs text-slate-500">Pada: {{ $invoice->rejected_at?->format('d M Y H:i') ?? '-' }}</div>
                        @if($invoice->rejection_reason)
                            <div class="text-xs text-red-600 dark:text-red-400 mt-1 p-2 bg-red-50 dark:bg-red-900/20 rounded">{{ $invoice->rejection_reason }}</div>
                        @endif
                    @endif
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">Catatan: {{ $invoice->notes ?: '-' }}</div>

                @if($invoice->tax_amount > 0)
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="font-semibold mb-1">Faktur Pajak</div>
                        @if($invoice->tax_invoice_status === 'none')
                            <div class="text-slate-500 italic">Belum direquest</div>
                            @if($invoice->status === 'sent')
                                <form action="{{ route('tax-invoices.store') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="invoice_ids[]" value="{{ $invoice->id }}">
                                    <button type="submit" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 transition-colors">
                                        Request Faktur Pajak
                                    </button>
                                </form>
                            @endif
                        @elseif($invoice->tax_invoice_status === 'requested')
                            <div class="flex items-center gap-2">
                                <span class="badge bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Requested</span>
                                <a href="{{ route('tax-invoices.show', $invoice->taxInvoiceRequest) }}" class="text-xs text-blue-600 hover:underline">Lihat Request</a>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Diajukan pada: {{ $invoice->tax_requested_at->format('d M Y H:i') }}</div>
                        @elseif($invoice->tax_invoice_status === 'completed')
                            <div class="flex items-center gap-2">
                                <span class="badge bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Completed</span>
                                <a href="{{ route('tax-invoices.show', $invoice->taxInvoiceRequest) }}" class="text-xs text-blue-600 hover:underline">Lihat Detail</a>
                            </div>
                            <div class="font-mono font-medium mt-1">{{ $invoice->tax_invoice_number }}</div>
                            <div class="text-xs text-slate-500">Tanggal: {{ $invoice->tax_invoice_date->format('d M Y') }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </x-card>
        <div class="md:col-span-2">
            <x-card title="Items">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-slate-500">
                                <th class="px-2 py-1">Deskripsi</th>
                                <th class="px-2 py-1">Qty</th>
                                <th class="px-2 py-1">Harga</th>
                                <th class="px-2 py-1">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $it)
                                <tr class="border-t border-slate-200 dark:border-slate-800">
                                    <td class="px-2 py-1">{{ $it->description }}</td>
                                    <td class="px-2 py-1">{{ $it->quantity }}</td>
                                    <td class="px-2 py-1">{{ number_format($it->unit_price, 2, ',', '.') }}</td>
                                    <td class="px-2 py-1">{{ number_format($it->amount, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-md w-full mx-4">
            <form method="post" action="{{ route('invoices.reject', $invoice) }}">
                @csrf
                @method('PATCH')
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Reject Invoice</h3>
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium mb-2">Alasan Penolakan</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white" placeholder="Masukkan alasan penolakan invoice..."></textarea>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Reject Invoice
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
