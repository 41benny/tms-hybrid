@extends('layouts.app', ['title' => 'Permintaan Faktur Pajak'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Permintaan Faktur Pajak</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola permintaan dan penerbitan faktur pajak</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tax-invoices.export', request()->all()) }}" class="px-3 py-2 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('tax-invoices.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Permintaan Baru
            </a>
        </div>
    </div>

    <x-card>
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Semua Status</option>
                <option value="requested" {{ request('status') === 'requested' ? 'selected' : '' }}>Requested</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <input type="date" name="from" value="{{ request('from') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" placeholder="Dari">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" placeholder="Sampai">

            <input type="text" name="customer" value="{{ request('customer') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" placeholder="Nama Customer...">

            <button type="submit" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">Filter</button>
        </form>
    </x-card>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">No. Request</th>
                    <th class="px-4 py-2">Tanggal Request</th>
                    <th class="px-4 py-2">No. Invoice</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">NPWP</th>
                    <th class="px-4 py-2 text-right">DPP</th>
                    <th class="px-4 py-2 text-right">PPN</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">No. Faktur Pajak</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-2 font-medium">
                            <a href="{{ route('tax-invoices.show', $req) }}" class="text-blue-600 hover:underline">
                                {{ $req->request_number }}
                            </a>
                        </td>
                        <td class="px-4 py-2">{{ $req->requested_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('invoices.show', $req->invoice_id) }}" class="text-slate-600 hover:text-blue-600 hover:underline" target="_blank">
                                {{ $req->invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-4 py-2">
                            {{ $req->customer_name }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $req->customer_npwp ?? '-' }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            {{ number_format($req->dpp, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            {{ number_format($req->ppn, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 text-right font-semibold">
                            {{ number_format($req->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2">
                            @if($req->status === 'requested')
                                <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 text-xs">
                                    Requested
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-xs">
                                    Completed
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($req->tax_invoice_number)
                                <div>{{ $req->tax_invoice_number }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $req->tax_invoice_date->format('d/m/Y') }}
                                </div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                @if($req->tax_invoice_file_path)
                                    <button onclick="previewPDF('{{ route('tax-invoices.preview', $req) }}')"
                                            class="text-indigo-600 hover:text-indigo-700"
                                            title="Preview Faktur Pajak">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                @endif
                                @if($req->status === 'requested')
                                    <a href="{{ route('tax-invoices.complete', $req) }}" class="text-blue-600 hover:text-blue-700" title="Input Faktur Pajak">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-8 text-slate-500">
                            Tidak ada data permintaan faktur pajak
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>

    {{-- PDF Preview Modal --}}
    <div id="pdfPreviewModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-2xl w-full max-w-6xl h-[90vh] flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Preview Faktur Pajak</h3>
                <button onclick="closePDFPreview()" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- PDF Viewer --}}
            <div class="flex-1 overflow-hidden">
                <iframe id="pdfFrame" class="w-full h-full" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    <script>
    function previewPDF(url) {
        const modal = document.getElementById('pdfPreviewModal');
        const iframe = document.getElementById('pdfFrame');
        iframe.src = url;
        modal.classList.remove('hidden');
    }

    function closePDFPreview() {
        const modal = document.getElementById('pdfPreviewModal');
        const iframe = document.getElementById('pdfFrame');
        iframe.src = '';
        modal.classList.add('hidden');
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePDFPreview();
        }
    });

    // Close on backdrop click
    document.getElementById('pdfPreviewModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closePDFPreview();
        }
    });
    </script>
@endsection
