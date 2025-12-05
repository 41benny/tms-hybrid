@php
    /** @var \App\Models\Finance\Invoice $invoice */
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 12px;
            color: #1f2933;
            background-color: #ffffff;
            margin: 0;
            padding: 24px;
        }
        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
        }
        h1,h2,h3,h4 { margin: 0; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-start { align-items: flex-start; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .text-right { text-align: right; }
        .text-sm { font-size: 11px; }
        .text-xs { font-size: 10px; }
        .font-bold { font-weight: 700; }
        .border { border: 1px solid #d0d7de; }
        .border-bottom { border-bottom: 1px solid #d0d7de; }
        .rounded { border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; }
        th { background: #f3f4f6; font-weight: 600; font-size: 11px; text-align: left; }
        tr:nth-child(even) td { background: #f9fafb; }
        .no-border td { border: none; background: transparent !important; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.08);
            z-index: -1;
            pointer-events: none;
            user-select: none;
        }
    </style>
</head>
<body>
@if($isDraft ?? false)
    <div class="watermark">DRAFT</div>
@endif
<div class="invoice-wrapper">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h1>INVOICE</h1>
            <div class="mt-2 text-sm">
                <div><span class="font-bold">Nomor:</span> {{ $invoice->invoice_number }}</div>
                <div><span class="font-bold">Tanggal:</span> {{ $invoice->invoice_date?->format('d M Y') }}</div>
                <div><span class="font-bold">Jatuh Tempo:</span> {{ $invoice->due_date?->format('d M Y') }}</div>
            </div>
        </div>
        <div class="text-right">
            <div class="font-bold mb-1">Kepada Yth:</div>
            <div>{{ $invoice->customer->name ?? '-' }}</div>
            @if(!empty($invoice->customer->address))
                <div class="text-sm mt-1" style="white-space: pre-line;">
                    {{ $invoice->customer->address }}
                </div>
            @endif
            @if(!empty($invoice->customer->phone) || !empty($invoice->customer->email))
                <div class="text-xs mt-1">
                    @if($invoice->customer->phone)
                        Telp: {{ $invoice->customer->phone }}
                    @endif
                    @if($invoice->customer->email)
                        {{ $invoice->customer->phone ? ' | ' : '' }}Email: {{ $invoice->customer->email }}
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <table class="border rounded">
            <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Deskripsi</th>
                <th style="width: 15%;" class="text-right">Qty</th>
                <th style="width: 15%;" class="text-right">Harga</th>
                <th style="width: 20%;" class="text-right">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @php $row = 1; @endphp
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $row++ }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            @if($invoice->items->isEmpty())
                <tr>
                    <td colspan="5" class="text-xs">Tidak ada item.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    @php
        $subtotal = $invoice->subtotal ?? $invoice->items->sum('amount');
        $tax = $invoice->tax_amount ?? 0;
        $discount = $invoice->discount_amount ?? 0;
        $pph23 = $invoice->pph23_amount ?? 0;
        $total = $invoice->total_amount ?? ($subtotal + $tax - $discount);
        $netPayable = $total - $pph23;
    @endphp

    <div class="flex justify-between mt-4">
        <div style="width: 55%;">
            @if($invoice->notes)
                <div class="font-bold mb-1">Catatan:</div>
                <div class="text-sm" style="white-space: pre-line;">{{ $invoice->notes }}</div>
            @endif
        </div>
        <div style="width: 40%;">
            <table>
                <tr>
                    <td class="text-sm">Subtotal</td>
                    <td class="text-right text-sm">{{ number_format($subtotal, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-sm">PPN</td>
                    <td class="text-right text-sm">{{ number_format($tax, 2, ',', '.') }}</td>
                </tr>
                @if($discount > 0)
                <tr>
                    <td class="text-sm">Diskon</td>
                    <td class="text-right text-sm">-{{ number_format($discount, 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="border-bottom">
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td class="font-bold">Total Tagihan</td>
                    <td class="text-right font-bold">{{ number_format($total, 2, ',', '.') }}</td>
                </tr>
                @if($invoice->show_pph23)
                <tr>
                    <td class="text-sm" style="color: #d97706;">PPh 23</td>
                    <td class="text-right text-sm" style="color: #d97706;">-{{ number_format($pph23, 2, ',', '.') }}</td>
                </tr>
                <tr class="border-bottom">
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td class="font-bold" style="color: #059669;">Sisa Tagihan (Net)</td>
                    <td class="text-right font-bold" style="color: #059669;">{{ number_format($netPayable, 2, ',', '.') }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="mt-6 flex justify-between">
        <div class="text-xs">
            Dicetak pada: {{ now()->format('d M Y H:i') }}<br>
            Dibuat oleh: {{ $invoice->createdBy->name ?? '-' }}
        </div>
        <div class="text-right text-sm" style="width: 40%;">
            <div class="mb-6">Hormat kami,</div>
            <div style="margin-top:48px;border-top:1px solid #d0d7de;padding-top:4px;">(________________________)</div>
        </div>
    </div>

    @if($invoice->isApproved())
    <div class="mt-6 pt-4" style="margin-top: 24px; padding-top: 16px; border-top: 2px solid #059669;">
        <div class="text-xs" style="font-size: 10px; color: #059669;">
            <div class="font-bold mb-1" style="font-weight: 700; margin-bottom: 4px; font-size: 11px;">✓ INVOICE APPROVED</div>
            <div style="color: #64748b; line-height: 1.5;">
                Invoice ini telah mendapatkan persetujuan dari <strong>{{ $invoice->approvedBy->name }}</strong> pada <strong>{{ $invoice->approved_at->format('d F Y, H:i') }} WIB</strong>.
            </div>
            <div style="color: #64748b; margin-top: 4px; line-height: 1.5;">
                Dokumen ini sah dan dapat digunakan sebagai bukti transaksi resmi.
            </div>
        </div>
    </div>
    @endif

    <div class="mt-4 text-center no-print">
        <button onclick="window.print()" style="padding:6px 12px;border:1px solid #d0d7de;border-radius:4px;background:#111827;color:#fff;cursor:pointer;">
            Print
        </button>
    </div>
</div>
</body>
</html>

