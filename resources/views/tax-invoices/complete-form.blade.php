@extends('layouts.app', ['title' => 'Input Faktur Pajak'])

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('tax-invoices.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Permintaan Faktur Pajak</a>
        <span>/</span>
        <a href="{{ route('tax-invoices.show', $taxInvoiceRequest) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $taxInvoiceRequest->request_number }}</a>
        <span>/</span>
        <span>Input Faktur</span>
    </div>

    {{-- Header --}}
    <div>
        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Input Faktur Pajak</div>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Masukkan nomor faktur pajak dari Coretax DJP (atau e-Faktur untuk faktur lama)</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('tax-invoices.update-complete', $taxInvoiceRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <x-card>
                    <div class="space-y-8">
                        {{-- File Upload Section --}}
                        <div class="pb-6 border-b border-slate-200 dark:border-slate-700">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                Upload Faktur Pajak (Opsional)
                            </label>
                            <div class="relative">
                                <input type="file" name="tax_invoice_file"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="block w-full text-sm text-slate-500 dark:text-slate-400
                                              file:mr-4 file:py-2.5 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-sm file:font-medium
                                              file:bg-indigo-50 file:text-indigo-700
                                              dark:file:bg-indigo-900/20 dark:file:text-indigo-400
                                              hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/30
                                              file:cursor-pointer cursor-pointer
                                              border border-slate-300 dark:border-slate-600 rounded-lg
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400
                                              @error('tax_invoice_file') border-red-500 @enderror"
                                       id="taxInvoiceFile">
                            </div>
                            @error('tax_invoice_file')
                                <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex items-start gap-1.5">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Upload file faktur pajak dari Coretax DJP (PDF/Image, max 5MB). Sistem akan otomatis extract nomor faktur.</span>
                            </p>

                            {{-- Loading state --}}
                            <div id="extractionLoading" class="hidden mt-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center gap-3">
                                    <svg class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Mengekstrak nomor faktur dengan AI...</span>
                                </div>
                            </div>

                            {{-- Success/Error messages --}}
                            <div id="extractionSuccess" class="hidden mt-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 text-sm font-medium"></div>

                            {{-- Extraction Warning --}}
                            <div id="extractionWarning" class="hidden mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg flex items-start gap-2">
                                <svg class="w-5 h-5 text-yellow-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div class="text-sm text-yellow-700 dark:text-yellow-300">
                                    <p class="font-medium">Perhatian:</p>
                                    <ul id="warningList" class="list-disc list-inside mt-1 space-y-1"></ul>
                                </div>
                            </div>

                            <div id="extractionError" class="hidden mt-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200 text-sm font-medium"></div>
                        </div>

                        {{-- Form Fields --}}
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Nomor Faktur Pajak <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="tax_invoice_number"
                                       class="block w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600
                                              bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100
                                              placeholder-slate-400 dark:placeholder-slate-500
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent
                                              transition-colors
                                              @error('tax_invoice_number') border-red-500 ring-2 ring-red-200 @enderror"
                                       value="{{ old('tax_invoice_number') }}"
                                       placeholder="010.000-24.00000001"
                                       required>
                                @error('tax_invoice_number')
                                    <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Format Coretax: 18 karakter (contoh: 080225003715972Z8) atau e-Faktur lama: 16 digit (010.000-YY.XXXXXXXX)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Tanggal Faktur Pajak <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tax_invoice_date"
                                       class="block w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600
                                              bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent
                                              transition-colors
                                              @error('tax_invoice_date') border-red-500 ring-2 ring-red-200 @enderror"
                                       value="{{ old('tax_invoice_date', now()->format('Y-m-d')) }}"
                                       required>
                                @error('tax_invoice_date')
                                    <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Catatan (Opsional)
                                </label>
                                <textarea name="notes"
                                          class="block w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600
                                                 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100
                                                 placeholder-slate-400 dark:placeholder-slate-500
                                                 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent
                                                 transition-colors resize-none"
                                          rows="4"
                                          placeholder="Tambahkan catatan jika perlu...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-6 border-t border-slate-200 dark:border-slate-700 flex gap-3 justify-end">
                            <a href="{{ route('tax-invoices.show', $taxInvoiceRequest) }}"
                               class="px-6 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600
                                      text-slate-700 dark:text-slate-300 font-medium
                                      hover:bg-slate-50 dark:hover:bg-slate-800
                                      transition-colors">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700
                                           text-white font-medium shadow-lg shadow-indigo-500/30
                                           hover:shadow-xl hover:shadow-indigo-500/50
                                           transition-all">
                                Simpan & Selesaikan
                            </button>
                        </div>
                    </div>
                </x-card>
            </form>
        </div>

        {{-- Right Column: Summary --}}
        <div>
            <x-card>
                <div class="space-y-6">
                    {{-- Title --}}
                    <div class="pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Ringkasan Data</h3>
                    </div>

                    {{-- Customer Info --}}
                    <div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Customer</div>
                        <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $taxInvoiceRequest->customer_name }}</div>
                        <div class="font-mono text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $taxInvoiceRequest->customer_npwp }}</div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600 dark:text-slate-400">DPP</span>
                            <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ number_format($taxInvoiceRequest->dpp, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                                <p class="text-blue-800 dark:text-blue-200 text-xs leading-relaxed">Pastikan data yang diinput sesuai dengan data di aplikasi Coretax DJP (atau e-Faktur untuk faktur lama).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>

<script>
document.getElementById('taxInvoiceFile')?.addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const loading = document.getElementById('extractionLoading');
    const success = document.getElementById('extractionSuccess');
    const error = document.getElementById('extractionError');
    const numberInput = document.querySelector('input[name="tax_invoice_number"]');
    const dateInput = document.querySelector('input[name="tax_invoice_date"]');

    // Show loading
    loading.classList.remove('hidden');
    success.classList.add('hidden');
    error.classList.add('hidden');
    document.getElementById('extractionWarning')?.classList.add('hidden');

    // Create FormData
    const formData = new FormData();
    formData.append('file', file);
    formData.append('request_id', '{{ $taxInvoiceRequest->id }}');
    formData.append('_token', '{{ csrf_token() }}');

    try {
        const response = await fetch('{{ route("tax-invoices.extract") }}', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        loading.classList.add('hidden');

        if (data.success && data.data) {
            // Auto-fill fields
            if (data.data.number) numberInput.value = data.data.number;
            if (data.data.date) dateInput.value = data.data.date;

            success.innerHTML = `
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Nomor faktur berhasil diekstrak! Silakan review dan edit jika perlu.</span>
                </div>
            `;
            success.classList.remove('hidden');

            // Handle warnings
            const warningDiv = document.getElementById('extractionWarning');
            const warningList = document.getElementById('warningList');

            if (data.warnings && data.warnings.length > 0) {
                warningList.innerHTML = data.warnings.map(w => `<li>${w}</li>`).join('');
                warningDiv.classList.remove('hidden');
            } else {
                warningDiv.classList.add('hidden');
            }
        } else {
            // Show specific error message
            let errorMessage = data.message || 'Auto-extract gagal. Silakan input nomor faktur manual.';
            let errorIcon = '⚠️';

            // Customize icon based on error type
            if (data.error_type === 'api_key_missing') {
                errorIcon = '🔑';
            } else if (data.error_type === 'network_error') {
                errorIcon = '🌐';
            } else if (data.error_type === 'timeout') {
                errorIcon = '⏱️';
            } else if (data.error_type === 'invalid_format') {
                errorIcon = '📄';
            } else if (data.error_type === 'file_too_large') {
                errorIcon = '📦';
            }

            error.innerHTML = `
                <div class="flex items-start gap-2">
                    <span class="text-xl flex-shrink-0">${errorIcon}</span>
                    <div>
                        <p class="font-semibold mb-1">Extraction Gagal</p>
                        <p class="text-xs">${errorMessage}</p>
                    </div>
                </div>
            `;
            error.classList.remove('hidden');
        }
    } catch (err) {
        console.error('Extraction error:', err);
        loading.classList.add('hidden');
        error.innerHTML = `
            <div class="flex items-start gap-2">
                <span class="text-xl flex-shrink-0">❌</span>
                <div>
                    <p class="font-semibold mb-1">Error Aplikasi</p>
                    <p class="text-xs">Terjadi kesalahan saat memproses data. Silakan coba lagi atau input manual.</p>
                </div>
            </div>
        `;
        error.classList.remove('hidden');
    }
});
</script>
@endsection
