<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Voucher Kas/Bank - {{ $companyInfo['name'] ?? 'PT. Vintama Perkasa Nusantara' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root{
            /* ====== THEME: EMAS ELEGAN ====== */
            --gold:#C9A227;
            --gold-deep:#A88418;
            --ink:#1a1a1a;
            --muted:#6b7280;
            --line:#e6e8ef;
            --soft:#fffcf1;   /* latar lembut */
            --soft2:#faf7e7;  /* latar header kartu */
            --success:#198754;
            --danger:#dc3545;
        }
        *{ -webkit-print-color-adjust: exact; print-color-adjust: exact; box-sizing: border-box; }

        body{
            margin:0; background:#fff; color:#000;
            font-family: "Inter","Segoe UI",Tahoma,Arial,sans-serif;
            font-size: 13px; line-height: 1.5;
            font-weight: 500;
        }

        /* Lembaran sentris (ukuran sama persis contoh kamu) */
        .sheet{
            max-width: 900px;
            margin: 0 auto 28px;
            background:#fff;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            overflow:hidden;
        }

        /* Ribbon emas di atas */
        .ribbon{ height:6px; background:linear-gradient(90deg,var(--gold),var(--gold-deep)); }

        /* Toolbar non-print */
        .toolbar{
            position: sticky; top: 0; z-index: 10;
            background: linear-gradient(90deg, rgba(201,162,39,.07), rgba(168,132,24,.07));
            border-bottom: 1px solid var(--line);
            padding: 8px 12px; display: flex; gap: 8px; justify-content: flex-end;
        }
        .btn{
            display:inline-flex; align-items:center; gap:8px;
            background:#fff; border:1px solid var(--line);
            color: #7c5d00; padding:8px 14px; border-radius:10px;
            font-weight:700; cursor:pointer;
        }
        .btn:hover{ background:#fcfbf6; }
        .btn-danger{ color:#fff; background: var(--danger); border-color: var(--danger); }
        .btn-danger:hover{ filter: brightness(.95); }

        /* Header (kop) */
        .header{
            display:grid; grid-template-columns: 1fr 280px; gap:16px; align-items:center;
            padding:18px 16px 14px 16px;
            border-bottom:1px solid var(--line);
        }
        .brand{ display:flex; gap:14px; align-items:flex-start; }
        .logo{ height:64px; width:auto; object-fit:contain; display:block; border-radius:10px; }
        .brand-info .name{ font-size:20px; font-weight:900; margin:0 0 4px 0; letter-spacing:.3px; }
        .brand-info .tagline{ margin:0 0 6px 0; color:var(--muted); font-size:12.5px; }
        .brand-info .contact{ margin:0; color:var(--muted); font-size:11.5px; }
        .doc-meta{
            border:1px solid var(--line); border-radius:12px; padding:10px 12px; background:#fff;
        }
        .doc-title{
            display:inline-block; margin-bottom:8px;
            background: linear-gradient(180deg,#fff,#fff 50%,#fef7e7);
            color:#111; font-weight:800; padding:7px 12px; border-radius:999px;
            border:1px solid var(--gold);
            box-shadow:0 0 0 3px rgba(201,162,39,.10);
            letter-spacing:.6px; font-size:12px;
        }
        .kv{ display:grid; grid-template-columns: 110px 1fr; gap:6px; font-size:13px; font-weight: 500; }
        .kv div{ padding:2px 0; color: #000; }

        /* Konten */
        .content{ padding:16px; }

        .grid-2{ display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
        .card{
            border:1px solid var(--line); border-radius:12px; background:#fff; overflow:hidden;
        }
        .card .head{
            margin:0; padding:10px 12px; font-weight:800; color:#7c5d00; font-size:12.5px;
            background:linear-gradient(180deg,#fff,#fff 45%,var(--soft2));
            border-bottom:1px solid var(--line);
        }
        .card .body{ padding:12px; color: #000; font-weight: 500; }

        .kv-sm{ display:grid; grid-template-columns: 120px 1fr; gap:6px; font-size:13px; font-weight: 500; }
        .kv-sm div{ padding:3px 0; color: #000; }
        .muted{ color:var(--muted); }

        /* Amount box */
        .amount{
            text-align:center; padding:16px 14px;
            border:1.5px solid var(--gold); border-radius:12px; background:#fff; margin-top:10px;
            box-shadow:0 0 0 3px rgba(201,162,39,.08) inset;
        }
        .amount .label{ font-size:12px; color:var(--muted); margin-bottom:6px; }
        .amount .value{ font-size:24px; font-weight:900; color:#000; letter-spacing:.3px; }
        .amount .words{
            margin-top:8px; font-size:11.5px; color:var(--muted);
            background: var(--soft); padding:8px; border-radius:8px; border:1px dashed #eadfb7;
        }

        /* Signatures */
        .signatures{ display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-top:18px; }
        .sig{
            position:relative; border:1px dashed #eadfb7; border-radius:14px; padding:14px; background:var(--soft);
        }
        .sig .role{
            position:absolute; top:-10px; left:12px; background:var(--soft);
            padding:2px 8px; border:1px solid #eadfb7; border-radius:999px; font-size:11px;
            color:#7c6a2b; font-weight:700; letter-spacing:.3px;
        }
        .sig .line{ border-bottom:2px solid #bca86a; margin:48px 6px 6px; }
        .sig .name{ text-align:center; font-size:12px; color:#6b7280; }

        .footer{
            margin:14px 16px 18px; text-align:center; font-size:11px; color:var(--muted);
            border-top:1px dashed var(--line); padding-top:10px;
        }

        .text-success{ color: var(--success); }
        .text-danger{ color: var(--danger); }

        @media print{
            .toolbar{ display:none !important; }
            .sheet{ box-shadow:none; border-radius:0; }
            @page{ margin: 14mm; }
            body{ font-size: 13px; color: #000; }
            .kv, .kv-sm { font-weight: 600; }
        }
    </style>
</head>
<body>
@php
    // Company info (fallback kalau $companyInfo tidak ada)
    $company   = $companyInfo['name']    ?? 'PT. Vintama Perkasa Nusantara';
    $tagline   = $companyInfo['tagline'] ?? 'Cash & Finance Management System';
    $address   = $companyInfo['address'] ?? 'Jl. Contoh No. 123, Jakarta';
    $phone     = $companyInfo['phone']   ?? null;
    $email     = $companyInfo['email']   ?? 'finance@vintama.co.id';

    // === Data voucher (dari $transaction versi TMS) ===
    $rekeningNama = optional($transaction->account)->name ?? '-';
    $rekeningNo   = optional($transaction->account)->account_number;
    $kategori     = ucwords(str_replace('_',' ', $transaction->sumber ?? '-'));
    $jenisLabel   = $transaction->jenis === 'cash_in' ? 'Penerimaan' : 'Pengeluaran';
    $jenisClass   = $transaction->jenis === 'cash_in' ? 'text-success' : 'text-danger';

    $penerima = $transaction->recipient_name ?: '-';
    if ($transaction->customer) {
        $penerima .= ' / '.($transaction->customer->name ?? '');
    } elseif ($transaction->vendor) {
        $penerima .= ' / '.($transaction->vendor->name ?? '');
    }

    // === Terbilang (fallback sederhana) ===
    $__words = null;
    try {
        if (class_exists(\Terbilang\Terbilang::class)) {
            $__words = \Terbilang\Terbilang::make($transaction->amount);
        }
    } catch (\Throwable $e) { $__words = null; }

    if (!$__words) {
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
        $__words = trim(terbilang_id_basic_tx((int)$transaction->amount));
    }
@endphp

{{-- Toolbar aksi (non-print) --}}
<div class="toolbar no-print">
    <button class="btn" onclick="window.print()">🖨️ Cetak</button>
    <button class="btn btn-danger" onclick="window.close()">✖️ Tutup</button>
</div>

<div class="sheet">
    <div class="ribbon"></div>

    {{-- Header --}}
    <div class="header">
        <div class="brand">
            @if(file_exists(public_path('images/logo/Logo.png')))
                <img src="{{ asset('images/logo/Logo.png') }}" alt="Logo" class="logo">
            @endif
            <div class="brand-info">
                <div class="name">{{ $company }}</div>
                @if($tagline)
                    <div class="tagline">{{ $tagline }}</div>
                @endif
                <p class="contact">
                    {{ $address }}
                    @if($phone) &nbsp;|&nbsp; Telp: {{ $phone }} @endif
                    @if($email) &nbsp;|&nbsp; Email: {{ $email }} @endif
                </p>
            </div>
        </div>
        <div class="doc-meta">
            <span class="doc-title">Voucher Kas / Bank</span>
            <div class="kv" style="margin-top:6px">
                <div>No. Voucher</div><div>: {{ $transaction->voucher_number }}</div>
                <div>Tanggal</div><div>: {{ $transaction->tanggal->format('d/m/Y') }}</div>
                <div>Jenis</div>
                <div>: <span class="{{ $jenisClass }}">{{ $jenisLabel }}</span></div>
            </div>
        </div>
    </div>

    {{-- Konten Utama --}}
    <div class="content">
        <div class="grid-2">
            {{-- KIRI --}}
            <div class="card">
                <div class="head">Informasi Voucher</div>
                <div class="body">
                    <div class="kv-sm">
                        <div class="muted">Rekening</div>
                        <div>: {{ $rekeningNama }}
                            @if($rekeningNo)
                                <div class="muted">{{ $rekeningNo }}</div>
                            @endif
                        </div>

                        <div class="muted">Kategori</div>
                        <div>: {{ $kategori }}</div>

                        <div class="muted">Penerima</div>
                        <div>: {{ $penerima }}</div>
                    </div>

                    <div class="amount">
                        <div class="label">
                            {{ $transaction->jenis === 'cash_in' ? 'Jumlah Diterima' : 'Jumlah Dibayar' }}
                        </div>
                        <div class="value">
                            {{ $transaction->jenis === 'cash_out' ? '- ' : '' }}Rp {{ number_format($transaction->amount,0,',','.') }}
                        </div>
                        @if(!empty($__words))
                            <div class="words">{{ ucwords($__words) }} Rupiah</div>
                        @endif

                        @if(($transaction->withholding_pph23 ?? 0) > 0)
                            <div style="margin-top:6px; font-size:11px; color:var(--muted);">
                                Termasuk potongan PPh 23:
                                <strong>Rp {{ number_format($transaction->withholding_pph23,0,',','.') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- KANAN --}}
            <div class="card">
                <div class="head">Keterangan</div>
                <div class="body">
                    <div class="kv-sm">
                        <div class="muted">Uraian</div>
                        <div>: {{ $transaction->description ?? '-' }}</div>

                        <div class="muted">No. Referensi</div>
                        <div>: {{ $transaction->reference_number ?? '-' }}</div>

                        <div class="muted">Dibuat Oleh</div>
                        <div>: {{ $transaction->created_by_name ?? (auth()->user()->name ?? '-') }}</div>

                        <div class="muted">Dicatat Pada</div>
                        <div>:
                            {{ optional($transaction->created_at)->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tanda Tangan --}}
        <div class="signatures">
            <div class="sig">
                <div class="role">Disetujui</div>
                <div class="line"></div>
                <div class="name">(.................................)</div>
            </div>
            <div class="sig">
                <div class="role">Dibuat</div>
                <div class="line"></div>
                <div class="name">
                    ({{ $transaction->created_by_name ?? (auth()->user()->name ?? '........................') }})
                </div>
            </div>
            <div class="sig">
                <div class="role">Diterima</div>
                <div class="line"></div>
                <div class="name">(.................................)</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} • {{ $company }} — {{ $address }}
        </div>
    </div>
</div>

{{-- Auto-print jika ?print=1 --}}
@if(request()->boolean('print'))
<script>
    window.addEventListener('load', function(){
        window.print();
        window.onafterprint = function(){ window.close(); };
        setTimeout(function(){ try{ window.close(); }catch(e){} }, 1500);
    });
</script>
@endif
</body>
</html>
