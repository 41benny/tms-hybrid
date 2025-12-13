<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $bill->vendor_bill_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --ink:#020617;
            --muted:#6b7280;
            --line:#e5e7eb;
            --primary:#111827;
            --accent:#4f46e5;
            --accent-soft:#eef2ff;
            --soft:#f9fafb;
        }
        *{ box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        body{
            margin:0;
            font-family:"Inter","Segoe UI",system-ui,-apple-system,BlinkMacSystemFont,"Helvetica Neue",Arial,sans-serif;
            font-size:13px;
            color:var(--ink);
            background:#f3f4f6;
        }
        .sheet{
            max-width:900px;
            margin:0 auto 24px;
            background:#fff;
            border-radius:14px;
            box-shadow:0 10px 30px rgba(15,23,42,.12);
            overflow:hidden;
        }
        .ribbon{
            height:6px;
            background:linear-gradient(90deg,#4f46e5,#a855f7);
        }
        .toolbar{
            position:sticky;
            top:0;
            z-index:10;
            background:linear-gradient(90deg,rgba(79,70,229,.06),rgba(168,85,247,.06));
            border-bottom:1px solid var(--line);
            padding:8px 12px;
            display:flex;
            justify-content:flex-end;
            gap:8px;
        }
        .btn{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:7px 12px;
            border-radius:999px;
            border:1px solid var(--line);
            background:#fff;
            font-size:12px;
            font-weight:600;
            color:var(--primary);
            cursor:pointer;
        }
        .btn-primary{
            border-color:transparent;
            background:linear-gradient(90deg,#4f46e5,#6366f1);
            color:#fff;
        }
        .btn span.icon{ font-size:13px; }

        .header{
            display:grid;
            grid-template-columns:1.6fr 1.2fr;
            gap:18px;
            padding:18px 18px 14px;
            border-bottom:1px solid var(--line);
        }
        .brand{
            display:flex;
            gap:12px;
        }
        .brand-logo{
            width:56px;
            height:56px;
            border-radius:14px;
            background:radial-gradient(circle at 20% 20%,#a5b4fc,#6366f1);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-weight:800;
            font-size:20px;
        }
        .brand-text h1{
            margin:0 0 4px;
            font-size:20px;
            font-weight:900;
            letter-spacing:.12em;
            text-transform:uppercase;
            color:var(--primary);
        }
        .brand-text .subtitle{
            margin:0;
            font-size:11px;
            color:var(--muted);
        }
        .brand-text .tag{
            margin-top:8px;
            display:inline-flex;
            align-items:center;
            padding:3px 10px;
            border-radius:999px;
            background:var(--accent-soft);
            color:var(--accent);
            font-size:11px;
            font-weight:600;
        }
        .doc-box{
            border:1px solid var(--line);
            border-radius:12px;
            padding:10px 12px;
            background:var(--soft);
        }
        .doc-title{
            display:inline-block;
            padding:5px 11px;
            border-radius:999px;
            background:#fff;
            border:1px solid rgba(79,70,229,.35);
            font-size:11px;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
            color:var(--accent);
            margin-bottom:6px;
        }
        .kv{
            display:grid;
            grid-template-columns:120px 1fr;
            gap:4px;
            font-size:12px;
        }
        .kv-label{ color:var(--muted); }
        .kv-value{ color:var(--primary); font-weight:600; }

        .content{ padding:16px 18px 20px; }
        .grid-2{
            display:grid;
            grid-template-columns:1.4fr 1.1fr;
            gap:16px;
        }
        .card{
            border:1px solid var(--line);
            border-radius:12px;
            background:#fff;
            overflow:hidden;
        }
        .card-head{
            padding:9px 12px;
            border-bottom:1px solid var(--line);
            background:linear-gradient(180deg,#fff,#eef2ff);
            font-size:12px;
            font-weight:700;
            color:var(--primary);
        }
        .card-body{ padding:11px 12px 12px; font-size:12px; }

        .table{
            width:100%;
            border-collapse:collapse;
            font-size:12px;
        }
        .table th,
        .table td{
            padding:7px 6px;
            border-bottom:1px solid #e5e7eb;
        }
        .table th{
            text-align:left;
            font-weight:600;
            color:var(--muted);
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.04em;
            background:#f9fafb;
        }
        .table tfoot td{
            font-weight:700;
            border-top:1px solid #d4d4d8;
        }

        .amount-box{
            border-radius:12px;
            padding:12px 12px 10px;
            border:1.5px solid rgba(79,70,229,.45);
            background:radial-gradient(circle at top left,#eef2ff,#fff);
        }
        .amount-label{
            font-size:11px;
            color:var(--muted);
            margin-bottom:4px;
        }
        .amount-value{
            font-size:22px;
            font-weight:800;
            color:var(--primary);
        }
        .amount-words{
            margin-top:6px;
            font-size:11px;
            color:var(--muted);
            padding:7px 8px;
            border-radius:9px;
            background:var(--soft);
            border:1px dashed #d4d4ff;
        }

        .notes{
            margin-top:10px;
            font-size:11px;
            color:var(--muted);
        }

        .signatures{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:14px;
            margin-top:18px;
        }
        .sig{
            border-radius:12px;
            border:1px dashed #cbd5f5;
            padding:12px 10px 10px;
            background:var(--soft);
            position:relative;
        }
        .sig-role{
            position:absolute;
            top:-9px;
            left:10px;
            padding:2px 8px;
            border-radius:999px;
            border:1px solid #cbd5f5;
            background:#f9fafb;
            font-size:10px;
            font-weight:700;
            letter-spacing:.06em;
            text-transform:uppercase;
            color:#4b5563;
        }
        .sig-line{
            margin:38px 8px 4px;
            border-bottom:1.5px solid #9ca3af;
        }
        .sig-name{
            text-align:center;
            font-size:11px;
            color:var(--muted);
        }

        .footer{
            margin:14px 18px 18px;
            padding-top:8px;
            border-top:1px dashed var(--line);
            font-size:11px;
            color:var(--muted);
            text-align:center;
        }

        @media print{
            .toolbar{ display:none !important; }
            .sheet{ box-shadow:none; border-radius:0; }
            body{ background:#fff; }
            @page{ margin:14mm; }
        }
    </style>
</head>
@php
    $vendor = $bill->vendor;
    $job = optional($bill->items->first())->shipmentLeg->jobOrder ?? null;
    $customer = $job?->customer;

    // DPP/PPN/PPH sudah dihitung di controller dan disimpan di $bill
    $dpp = $bill->dpp ?? 0;
    $ppn = $bill->ppn ?? 0;
    $pph = $bill->pph ?? 0;
    $total = $bill->total_amount ?? 0;

    // Terbilang sederhana (gunakan helper jika ada)
    $amountWords = null;
    try {
        if (class_exists(\Terbilang\Terbilang::class)) {
            $amountWords = \Terbilang\Terbilang::make($total).' rupiah';
        }
    } catch (\Throwable $e) {
        $amountWords = null;
    }
    if (!$amountWords) {
        if (!function_exists('terbilang_id_basic_tx')) {
            function terbilang_id_basic_tx($x){
                $x = (int) $x;
                $angka = ["","satu","dua","tiga","empat","lima","enam","tujuh","delapan","sembilan","sepuluh","sebelas"];
                if ($x < 12) return " ".$angka[$x];
                if ($x < 20) return terbilang_id_basic_tx($x-10)." belas";
                if ($x < 100) return terbilang_id_basic_tx(intval($x/10))." puluh".terbilang_id_basic_tx($x%10);
                if ($x < 200) return " seratus".terbilang_id_basic_tx($x-100);
                if ($x < 1000) return terbilang_id_basic_tx(intval($x/100))." ratus".terbilang_id_basic_tx($x%100);
                if ($x < 2000) return " seribu".terbilang_id_basic_tx($x-1000);
                if ($x < 1000000) return terbilang_id_basic_tx(intval($x/1000))." ribu".terbilang_id_basic_tx($x%1000);
                if ($x < 1000000000) return terbilang_id_basic_tx(intval($x/1000000))." juta".terbilang_id_basic_tx($x%1000000);
                if ($x < 1000000000000) return terbilang_id_basic_tx(intval($x/1000000000))." miliar".terbilang_id_basic_tx($x%1000000000);
                if ($x < 1000000000000000) return terbilang_id_basic_tx(intval($x/1000000000000))." triliun".terbilang_id_basic_tx($x%1000000000000);
                return "";
            }
        }
        $amountWords = trim(terbilang_id_basic_tx((int)$total)).' rupiah';
    }
@endphp
<body>
<div class="sheet">
    <div class="ribbon"></div>
    <div class="toolbar">
        <button class="btn" onclick="window.close && window.close()">
            <span class="icon">←</span> Tutup
        </button>
        <button class="btn btn-primary" onclick="window.print()">
            <span class="icon">🖨</span> Cetak
        </button>
    </div>

    <div class="header">
        <div class="brand">
            <div class="brand-logo">PO</div>
            <div class="brand-text">
                <h1>PURCHASE ORDER</h1>
                <p class="subtitle">SPK Vendor • Dasar penerbitan tagihan</p>
                @if($job)
                    <p class="subtitle">Job: {{ $job->job_number }} @if($customer) • Customer: {{ $customer->name }} @endif</p>
                @endif
                <span class="tag">{{ $bill->vendor_bill_number }}</span>
            </div>
        </div>

        <div class="doc-box">
            <div class="doc-title">Vendor & Order Info</div>
            <div class="kv">
                <div class="kv-label">Vendor</div>
                <div class="kv-value">{{ $vendor->name ?? '-' }}</div>
                <div class="kv-label">Tanggal PO</div>
                <div class="kv-value">{{ $bill->bill_date->format('d M Y') }}</div>
                <div class="kv-label">Jatuh Tempo</div>
                <div class="kv-value">{{ optional($bill->due_date)->format('d M Y') ?: '-' }}</div>
                <div class="kv-label">Status</div>
                <div class="kv-value">{{ strtoupper(str_replace('_',' ', $bill->status)) }}</div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="grid-2">
            {{-- Left: Ringkasan nilai & terbilang --}}
            <div class="card">
                <div class="card-head">Ringkasan Nilai Purchase Order</div>
                <div class="card-body">
                    <div class="amount-box">
                        <div class="amount-label">Total Nilai Purchase Order</div>
                        <div class="amount-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
                        <div class="amount-words">Terbilang: {{ ucfirst($amountWords) }}</div>
                    </div>

                    <div class="notes">
                        Dokumen ini merupakan SPK Vendor / Purchase Order yang menjadi dasar vendor menerbitkan tagihan
                        atas jasa dan biaya yang tercantum di bawah. Mohon pastikan nomor PO dicantumkan di invoice.
                    </div>
                </div>
            </div>

            {{-- Right: Ringkasan DPP / PPN / PPh --}}
            <div class="card">
                <div class="card-head">Struktur Nilai (DPP / PPN / PPh)</div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                        <tr>
                            <td>DPP</td>
                            <td style="text-align:right">Rp {{ number_format($dpp, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>PPN</td>
                            <td style="text-align:right">Rp {{ number_format($ppn, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>PPh 23 (dipotong)</td>
                            <td style="text-align:right">Rp {{ number_format($pph, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Purchase Order</strong></td>
                            <td style="text-align:right"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Detail items --}}
        <div class="card" style="margin-top:16px;">
            <div class="card-head">Rincian Jasa / Biaya</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width:50%;">Deskripsi</th>
                        <th style="width:12%; text-align:right;">Qty</th>
                        <th style="width:18%; text-align:right;">Harga</th>
                        <th style="width:20%; text-align:right;">Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bill->items as $it)
                        @php
                            $desc = strtolower($it->description);
                            $isTaxItem = str_contains($desc, 'ppn') || str_contains($desc, 'pph');
                        @endphp
                        @if($isTaxItem) @continue @endif
                        <tr>
                            <td>
                                <div>{{ $it->description }}</div>
                                @if($it->shipmentLeg)
                                    <div style="font-size:11px; color:var(--muted); margin-top:2px;">
                                        @if($it->shipmentLeg->equipment)
                                            Unit: {{ $it->shipmentLeg->equipment->name }} •
                                        @endif
                                        Leg: {{ $it->shipmentLeg->leg_code }}
                                        @if($it->shipmentLeg->jobOrder)
                                            • Job: {{ $it->shipmentLeg->jobOrder->job_number }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td style="text-align:right;">{{ number_format($it->qty, 2, ',', '.') }}</td>
                            <td style="text-align:right;">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                            <td style="text-align:right;">
                                {{ $it->subtotal < 0 ? '-' : '' }}Rp {{ number_format(abs($it->subtotal), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;"><strong>Total</strong></td>
                        <td style="text-align:right;"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="signatures">
            <div class="sig">
                <div class="sig-role">Disiapkan</div>
                <div class="sig-line"></div>
                <div class="sig-name">Purchasing / Operations</div>
            </div>
            <div class="sig">
                <div class="sig-role">Disetujui</div>
                <div class="sig-line"></div>
                <div class="sig-name">Finance / Manager</div>
            </div>
            <div class="sig">
                <div class="sig-role">Diterima Vendor</div>
                <div class="sig-line"></div>
                <div class="sig-name">Nama & Cap Vendor</div>
            </div>
        </div>

        <div class="footer">
            Purchase Order / SPK Vendor ini tidak menggantikan invoice. Vendor wajib menerbitkan faktur/tagihan resmi dengan mencantumkan nomor PO di atas.
        </div>
    </div>
</div>
</body>
</html>

