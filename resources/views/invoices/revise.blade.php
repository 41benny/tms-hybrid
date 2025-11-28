@extends('layouts.app', ['title' => 'Revise Invoice'])

@section('content')
<x-card>
    <x-slot:header>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold">Revise Invoice: {{ $invoice->invoice_number }}</h2>
            <a href="{{ route('invoices.show', $invoice) }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                ← Back to Invoice
            </a>
        </div>
    </x-slot:header>
    
    @if($periodClosed)
        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Periode Akuntansi Sudah Ditutup</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Periode akuntansi untuk invoice ini ({{ $invoice->invoice_date->format('F Y') }}) sudah ditutup.
                        Revisi invoice pada periode yang sudah ditutup memerlukan koordinasi dengan divisi accounting.
                    </p>
                </div>
            </div>
        </div>
    @endif
    
    <form method="POST" action="{{ route('invoices.store-revision', $invoice) }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-2">Invoice Number (Current)</label>
                <input type="text" value="{{ $invoice->invoice_number }}" disabled class="w-full px-3 py-2 border rounded-lg bg-slate-100 dark:bg-slate-800">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Invoice Number (After Revision)</label>
                <input type="text" value="{{ $invoice->getNextRevisionNumber() }}" disabled class="w-full px-3 py-2 border rounded-lg bg-blue-50 dark:bg-blue-900/30 font-bold text-blue-600 dark:text-blue-400">
                <p class="text-xs text-slate-500 mt-1">Revision {{ $invoice->revision_number + 1 }}</p>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="revision_reason" class="block text-sm font-medium mb-2">
                Alasan Revisi <span class="text-red-500">*</span>
            </label>
            <textarea 
                id="revision_reason" 
                name="revision_reason" 
                rows="4" 
                required 
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Jelaskan alasan revisi invoice ini..."
            >{{ old('revision_reason') }}</textarea>
            @error('revision_reason')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        @if($periodClosed)
            <div class="mb-4">
                <label class="flex items-start gap-2">
                    <input type="checkbox" name="confirm_period_closed" value="1" class="mt-1" required>
                    <span class="text-sm">
                        Saya konfirmasi bahwa sudah berkoordinasi dengan divisi accounting terkait revisi invoice pada periode yang sudah ditutup.
                    </span>
                </label>
                @error('confirm_period_closed')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Catatan Penting:</h4>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
                <li>Status invoice akan kembali ke <strong>Draft</strong></li>
                <li>Approval status akan di-reset</li>
                <li>Invoice harus di-submit kembali untuk approval</li>
                <li>Nomor invoice akan berubah dengan tambahan revision number</li>
                <li>Anda dapat mengedit semua field invoice setelah revisi</li>
            </ul>
        </div>
        
        <div class="flex gap-2 justify-end">
            <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                Revise Invoice
            </button>
        </div>
    </form>
</x-card>
@endsection
