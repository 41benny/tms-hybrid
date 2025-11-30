<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK Uang Jalan - {{ $leg->jobOrder->job_number ?? '' }} / {{ $leg->leg_code }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --ink:#020617;
            --muted:#6b7280;
            --line:#e5e7eb;
            --primary:#0f172a;
            --accent:#6366f1;
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
            background:linear-gradient(90deg,#4f46e5,#0ea5e9);
        }
        .toolbar{
            position:sticky;
            top:0;
            z-index:10;
            background:linear-gradient(90deg,rgba(79,70,229,.06),rgba(14,165,233,.06));
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
            background:linear-gradient(90deg,#4f46e5,#0ea5e9);
            color:#fff;
        }
        .btn span.icon{ font-size:13px; }
        .header{
            display:grid;
            grid-template-columns:1.5fr 1.1fr;
            gap:16px;
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
            margin:0 0 2px;
            font-size:18px;
            font-weight:800;
            letter-spacing:.03em;
            color:var(--primary);
        }
        .brand-text p{
            margin:0;
            font-size:11px;
            color:var(--muted);
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
            background:var(--accent-soft);
            border:1px solid rgba(79,70,229,.25);
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
            grid-template-columns:1.4fr 1fr;
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
            background:linear-gradient(180deg,#fff,#f3f4ff);
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
            padding:6px 6px;
            border-bottom:1px solid #e5e7eb;
        }
        .table th{
            text-align:left;
            font-weight:600;
            color:var(--muted);
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.04em;
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
    $job = $leg->jobOrder;
    $mainCost = $leg->mainCost;
    $driver = $leg->driver;
    $truck = $leg->truck;
    $totalAdvance = ($mainCost->uang_jalan ?? 0) + ($mainCost->bbm ?? 0) + ($mainCost->toll ?? 0) + ($mainCost->other_costs ?? 0);

    // Terbilang: pakai library kalau ada, kalau tidak pakai helper simple
    $amountWords = null;
    try {
        if (class_exists(\Terbilang\Terbilang::class)) {
            $amountWords = \Terbilang\Terbilang::make($totalAdvance).' rupiah';
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
        $amountWords = trim(terbilang_id_basic_tx((int)$totalAdvance)).' rupiah';
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
            <div class="brand-logo">T</div>
            <div class="brand-text">
                <h1>INTERNAL TRUCKING ORDER</h1>
                <p>Surat Perintah Kerja Uang Jalan (Own Fleet)</p>
                <p>{{ $job->customer->name ?? 'Customer' }} • Job: {{ $job->job_number ?? '-' }}</p>
            </div>
        </div>
        <div class="doc-box">
            <div class="doc-title">SPK Uang Jalan</div>
            <div class="kv">
                <div class="kv-label">Nomor Leg</div>
                <div class="kv-value">{{ $leg->leg_code }} (Leg #{{ $leg->leg_number }})</div>
                <div class="kv-label">Tanggal</div>
                <div class="kv-value">{{ optional($leg->load_date)->format('d M Y') ?? now()->format('d M Y') }}</div>
                <div class="kv-label">Supir</div>
                <div class="kv-value">{{ optional($driver)->name ?: '-' }}</div>
                <div class="kv-label">Kendaraan</div>
                <div class="kv-value">
                    {{ optional($truck)->plate_number ?: '-' }}
                    {{ optional($truck)->nickname ? '• '.optional($truck)->nickname : '' }}
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="grid-2">
            {{-- Left column: route, cargo, approval --}}
            <div class="card">
                <div class="card-head">Detail Rute, Muatan & Persetujuan</div>
                <div class="card-body">
                    <div class="kv">
                        <div class="kv-label">Rute</div>
                        <div class="kv-value">{{ $job->origin ?? '-' }} → {{ $job->destination ?? '-' }}</div>
                        <div class="kv-label">Muatan</div>
                        <div class="kv-value">
                            {{ number_format($leg->quantity ?? 0, 2, ',', '.') }}
                            {{ optional($job->items->first())->cargo_type ?? 'unit' }}
                        </div>
                        <div class="kv-label">Kategori</div>
                        <div class="kv-value">Trucking Own Fleet</div>
                        <div class="kv-label">Catatan Job</div>
                        <div class="kv-value">{{ $job->notes ?? '-' }}</div>
                    </div>

                    <div style="margin-top:12px;">
                        <div class="amount-box">
                            <div class="amount-label">Disetujui untuk dibayarkan maksimal</div>
                            <div class="amount-value">Rp {{ number_format($totalAdvance, 0, ',', '.') }}</div>
                            <div class="amount-words">Terbilang: {{ ucfirst($amountWords) }}</div>
                        </div>

                        <div class="notes">
                            Catatan: Realisasi pembayaran dapat dilakukan bertahap (DP / pelunasan) melalui modul Driver Advance & Kas/Bank, namun tidak boleh melebihi batas maksimal di atas tanpa persetujuan ulang.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column: cost breakdown --}}
            <div class="card">
                <div class="card-head">Ringkasan Uang Jalan</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Komponen</th>
                            <th class="text-right">Jumlah</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Uang Jalan</td>
                            <td style="text-align:right">Rp {{ number_format($mainCost->uang_jalan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>BBM</td>
                            <td style="text-align:right">Rp {{ number_format($mainCost->bbm ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tol</td>
                            <td style="text-align:right">Rp {{ number_format($mainCost->toll ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Biaya Lain</td>
                            <td style="text-align:right">Rp {{ number_format($mainCost->other_costs ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Uang Jalan</strong></td>
                            <td style="text-align:right"><strong>Rp {{ number_format($totalAdvance, 0, ',', '.') }}</strong></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="sig">
                <div class="sig-role">Disiapkan</div>
                <div class="sig-line"></div>
                <div class="sig-name">Operations / Dispatcher</div>
            </div>
            <div class="sig">
                <div class="sig-role">Disetujui</div>
                <div class="sig-line"></div>
                <div class="sig-name">Finance / Manager</div>
            </div>
            <div class="sig">
                <div class="sig-role">Diterima Oleh</div>
                <div class="sig-line"></div>
                <div class="sig-name">Supir</div>
            </div>
        </div>

        <div class="footer">
            Dokumen internal untuk persetujuan uang jalan armada sendiri. Otomatis terhubung dengan Job Order dan Driver Advance di sistem.
        </div>
    </div>
</div>
</body>
</html>

