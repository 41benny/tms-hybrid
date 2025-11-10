@extends('layouts.app', ['title' => 'Transaksi Kas/Bank'])

@section('content')
<form method="post" action="{{ route('cash-banks.store') }}" class="space-y-4">
    @csrf
    <x-card title="Header">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ now()->format('Y-m-d') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Akun Kas/Bank</label>
                <select name="cash_bank_account_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Jenis</label>
                <select name="jenis" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    <option value="cash_in" {{ ($prefill['sumber'] ?? '')==='customer_payment' ? 'selected' : '' }}>Cash In</option>
                    <option value="cash_out" {{ in_array(($prefill['sumber'] ?? ''), ['vendor_payment','expense']) ? 'selected' : '' }}>Cash Out</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm mb-1">Sumber</label>
                <select name="sumber" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach(['customer_payment','vendor_payment','expense','other_in','other_out'] as $s)
                        <option value="{{ $s }}" @selected(($prefill['sumber'] ?? '')==$s)>{{ str_replace('_',' ', $s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Invoice (jika customer payment)</label>
                <select name="invoice_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($invoices as $i)
                        <option value="{{ $i->id }}" @selected(($prefill['invoice_id'] ?? null)==$i->id)>{{ $i->invoice_number }} ({{ number_format($i->total_amount,2,',','.') }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Vendor Bill (jika vendor payment)</label>
                <select name="vendor_bill_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($vendorBills as $vb)
                        <option value="{{ $vb->id }}" @selected(($prefill['vendor_bill_id'] ?? null)==$vb->id)>{{ $vb->vendor_bill_number }} ({{ number_format($vb->total_amount,2,',','.') }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm mb-1">Akun Biaya (jika expense)</label>
                <select name="coa_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($coas as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Nominal</label>
                <input type="number" step="0.01" min="0" name="amount" value="{{ $prefill['amount'] ?? '' }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">No. Referensi</label>
                <input type="text" name="reference_number" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm mb-1">Deskripsi</label>
            <textarea name="description" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" rows="3"></textarea>
        </div>
    </x-card>

    <div class="flex justify-end gap-2">
        <a href="{{ route('cash-banks.index') }}" class="px-3 py-2 rounded border">Batal</a>
        <button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button>
    </div>
</form>
@endsection

