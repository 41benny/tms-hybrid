@extends('layouts.app', ['title' => 'Detail Pembelian Part'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $partPurchase->purchase_number }}</div>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $partPurchase->purchase_date->format('d M Y') }}</p>
                </div>
                <x-button :href="route('part-purchases.index')" variant="ghost" size="sm">Kembali</x-button>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Vendor</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $partPurchase->vendor->name ?? '-' }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">No Invoice</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $partPurchase->invoice_number ?? '-' }}</p>
            </div>
            @if($partPurchase->vendorBill)
                <div>
                    <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Vendor Bill</label>
                    <p>
                        <a href="{{ route('vendor-bills.show', $partPurchase->vendorBill) }}" class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline">
                            {{ $partPurchase->vendorBill->vendor_bill_number }}
                        </a>
                    </p>
                </div>
            @endif
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Status</label>
                <p>
                    <x-badge :variant="$partPurchase->status === 'received' ? 'success' : ($partPurchase->status === 'cancelled' ? 'danger' : 'default')">
                        {{ ucfirst($partPurchase->status) }}
                    </x-badge>
                </p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Total</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100 text-lg">{{ number_format($partPurchase->total_amount, 2, ',', '.') }}</p>
            </div>
            @if($partPurchase->is_direct_usage)
                <div class="md:col-span-2">
                    <x-badge variant="warning">Langsung Pakai (tidak masuk stok)</x-badge>
                </div>
            @endif
        </div>

        <div class="border-t border-slate-200 dark:border-[#2d2d2d] pt-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Item Pembelian</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-[#252525]">
                        <tr>
                            <th class="px-4 py-2 text-left">Part</th>
                            <th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2 text-right">Harga</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                        @foreach($partPurchase->items as $item)
                            <tr>
                                <td class="px-4 py-2">
                                    <div class="font-medium">{{ $item->part->code }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->part->name }}</div>
                                </td>
                                <td class="px-4 py-2 text-right">{{ number_format($item->quantity, 2) }} {{ $item->part->unit }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right font-semibold">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-[#252525] font-semibold">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right">Total</td>
                            <td class="px-4 py-2 text-right">{{ number_format($partPurchase->total_amount, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </x-card>
</div>
@endsection

