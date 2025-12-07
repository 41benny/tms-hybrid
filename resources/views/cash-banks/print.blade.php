<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Voucher Kas/Bank - {{ $companyInfo['name'] ?? 'PT. Vintama Perkasa Nusantara' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root{
            /* ====== THEME: GREY MODERN + BLUE ====== */
            --gold:#3B82F6;         /* biru utama */
            --gold-deep:#1D4ED8;    /* biru lebih dalam */
            --ink:#111827;          /* teks utama (slate-900) */
            --muted:#6b7280;        /* teks sekunder (slate-500) */
            --line:#e5e7eb;         /* border (gray-200) */
            --soft:#f9fafb;         /* latar lembut (gray-50) */
            --soft2:#f3f4f6;        /* latar header kartu (gray-100) */
            --success:#16a34a;      /* hijau modern */
            --danger:#ef4444;       /* tombol merah */
        }

        *{ -webkit-print-color-adjust: exact; print-color-adjust: exact; box-sizing: border-box; }

        body{
            margin:0;
            background:#e5e7eb; 
            color:var(--ink);
            font-family:"Inter","Segoe UI",Tahoma,Arial,sans-serif;
            font-size:13px; line-height:1.5;
        }

        .sheet{
            max-width:900px;
            margin:0 auto 28px;
            background:#fff;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(15,23,42,.12);
            overflow:hidden;
        }

        /* Ribbon biru */
        .ribbon{
            height:6px;
            background:linear-gradient(90deg,var(--gold),var(--gold-deep));
        }

        /* Toolbar */
        .toolbar{
            position:sticky; top:0; z-index:10;
            background:linear-gradient(90deg,rgba(59,130,246,.08),rgba(15,23,42,.03));
            border-bottom:1px solid var(--line);
            padding:8px 12px;
            display:flex; gap:8px; justify-content:flex-end;
        }
        .btn{
            display:inline-flex; align-items:center; gap:8px;
            background:#fff; border:1px solid var(--line);
            color:var(--ink);
            padding:8px 14px; border-radius:999px;
            font-size:12px; font-weight:600;
            cursor:pointer;
        }
        .btn:hover{ background:var(--soft2); }

        .btn-danger{
            color:#fff;
            background:var(--danger);
            border-color:transparent;
        }

        /* Header */
        .header{
            display:grid; grid-template-columns:1fr 280px;
            gap:16px; align-items:center;
            padding:18px 16px 14px 16px;
            border-bottom:1px solid var(--line);
            background: radial-gradient(circle at 0 0, rgba(59,130,246,.05), transparent 60%);
        }
        .brand{ display:flex; gap:14px; align-items:flex-start; }

        .logo{ height:64px; object-fit:contain; border-radius:10px; }

        .brand-info .name{ font-size:20px; font-weight:900; margin:0; }
        .brand-info .tagline{ font-size:12.5px; color:var(--muted); margin-bottom:4px; }
        .brand-info .contact{ font-size:11px; color:var(--muted); }

        .doc-meta{
            border:1px solid var(--line);
            border-radius:12px;
            padding:10px 12px;
            background:linear-gradient(135deg,#fff,var(--soft2));
        }
        .doc-title{
            display:inline-block; margin-bottom:8px;
            background:linear-gradient(135deg,#fff,rgba(59,130,246,.10));
            color:var(--ink); font-weight:800;
            padding:7px 12px; border-radius:999px;
            border:1px solid rgba(59,130,246,.5);
            box-shadow:0 0 0 3px rgba(59,130,246,.15);
            letter-spacing:.5px; font-size:12px; text-transform:uppercase;
        }

        .kv{ display:grid; grid-template-columns:110px 1fr; gap:6px; }

        /* Cards */
        .content{ padding:16px; background:linear-gradient(180deg,#fff,#f9fafb); }

        .grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }

        .card{
            border:1px solid var(--line);
            border-radius:12px;
            background:#fff;
        }
        .card .head{
            padding:10px 12px;
            font-weight:700;
            font-size:12.5px;
            color:var(--gold-deep); /* BIRU */
            background:linear-gradient(180deg,var(--soft2),#fff);
            border-bottom:1px solid var(--line);
            letter-spacing:.3px;
        }
        .card .body{ padding:12px; }

        .kv-sm{ display:grid; grid-template-columns:120px 1fr; gap:6px; }

        .muted{ color:var(--muted); }

        /* Amount Box */
        .amount-split-box-num{
            border:1.5px solid var(--gold);
            border-radius:10px;
            padding:10px;
            text-align:center;
            background:#fff;
            box-shadow:0 0 0 2px rgba(59,130,246,.06);
        }
        .amount-split-box-words{
            border:1px dashed var(--line);
            border-radius:10px;
            padding:10px;
            background:var(--soft);
            display:flex; align-items:center;
        }

        /* Signature */
        .signatures{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-top:18px; }

        .sig{
            border:1px dashed var(--line);
            background:var(--soft);
            border-radius:14px;
            padding:14px;
            position:relative;
        }
        .sig .role{
            position:absolute; top:-10px; left:12px;
            background:#fff;
            padding:2px 8px;
            border-radius:999px;
            font-size:11px;
            border:1px solid rgba(148,163,184,.6);
            color:var(--gold-deep); /* biru */
            font-weight:700;
        }
        .sig .line{
            border-bottom:2px solid rgba(148,163,184,.8);
            margin:48px 6px 6px;
        }

        .footer{
            margin:14px 16px 18px;
            text-align:center;
            font-size:11px;
            border-top:1px dashed var(--line);
            padding-top:10px;
            color:var(--muted);
        }

        @media print{
            .toolbar{ display:none !important; }
            body{ background:#fff; }
            .sheet{ box-shadow:none; border-radius:0; }
            @page{ margin:14mm; }
        }
    </style>
</head>
<body>
@php
    $company   = $companyInfo['name']    ?? 'PT. Vintama Perkasa Nusantara';
    $tagline   = $companyInfo['tagline'] ?? 'Cash & Finance Management System';
    $address   = $companyInfo['address'] ?? 'Jl. Contoh No. 123, Jakarta';

    $rekeningNama = optional($transaction->account)->name ?? '-';
    $rekeningNo   = optional($transaction->account)->account_number;
    $kategori     = ucwords(str_replace('_',' ', $transaction->sumber ?? '-'));

    // Get recipient name (avoid duplication)
    $penerima = $transaction->recipient_name ?: ($transaction->customer?->name ?? $transaction->vendor?->name ?? '-');

    // Terbilang function
    $__words = null;
    try{
        if(class_exists(\Terbilang\Terbilang::class)){
            $__words = \Terbilang\Terbilang::make($transaction->amount);
        }
    }catch(\Throwable $e){}
    
    // Fallback terbilang if library not available
    if(!$__words){
        if(!function_exists('terbilang_simple')){
            function terbilang_simple($x){
                $x = abs((int)$x);
                $angka = ["","satu","dua","tiga","empat","lima","enam","tujuh","delapan","sembilan","sepuluh","sebelas"];
                if($x < 12) return " ".$angka[$x];
                if($x < 20) return terbilang_simple($x-10)." belas";
                if($x < 100) return terbilang_simple((int)($x/10))." puluh".terbilang_simple($x%10);
                if($x < 200) return " seratus".terbilang_simple($x-100);
                if($x < 1000) return terbilang_simple((int)($x/100))." ratus".terbilang_simple($x%100);
                if($x < 2000) return " seribu".terbilang_simple($x-1000);
                if($x < 1000000) return terbilang_simple((int)($x/1000))." ribu".terbilang_simple($x%1000);
                if($x < 1000000000) return terbilang_simple((int)($x/1000000))." juta".terbilang_simple($x%1000000);
                if($x < 1000000000000) return terbilang_simple((int)($x/1000000000))." miliar".terbilang_simple($x%1000000000);
                return "";
            }
        }
        $__words = trim(terbilang_simple((int)$transaction->amount));
    }
@endphp

<div class="toolbar no-print">
    <button class="btn" onclick="window.print()">🖨️ Cetak</button>
    <button class="btn btn-danger" onclick="window.close()">✖️ Tutup</button>
</div>

@php
    $accName = strtolower(optional($transaction->account)->name ?? '');
    $isBank = str_contains($accName,'bank') || str_contains($accName,'bca') || str_contains($accName,'mandiri') || str_contains($accName,'bri');
    $voucherTitle = "Voucher " . ($isBank ? 'Bank' : 'Kas') . " " . ($transaction->jenis === 'cash_in' ? 'Masuk' : 'Keluar');
@endphp

<div class="sheet">
    <!-- Ribbon: Red for cash_out, Blue for cash_in -->
    <div class="ribbon" style="background: linear-gradient(90deg, {{ $transaction->jenis === 'cash_out' ? '#ef4444, #dc2626' : 'var(--gold), var(--gold-deep)' }})"></div>

    <div class="header">
        <div class="brand">
            @if(file_exists(public_path('images/logo/Logo.png')))
                <img src="{{ asset('images/logo/Logo.png') }}" class="logo">
            @endif
            <div class="brand-info">
                <div class="name">{{ $company }}</div>
                <div class="tagline">{{ $tagline }}</div>
                <div class="contact">{{ $address }}</div>
            </div>
        </div>

        <div class="doc-meta">
            <span class="doc-title">{{ $voucherTitle }}</span>
            <div class="kv">
                <div>No. Voucher</div><div>: {{ $transaction->voucher_number }}</div>
                <div>Tanggal</div><div>: {{ $transaction->tanggal->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="grid-2">
            <div class="card">
                <div class="head">Informasi Voucher</div>
                <div class="body">
                    <div class="kv-sm">
                        <div class="muted">Rekening</div>
                        <div>: {{ $rekeningNama }} @if($rekeningNo)<span class="muted">({{ $rekeningNo }})</span>@endif</div>

                        <div class="muted">Kategori</div>
                        <div>: {{ $kategori }}</div>

                        <div class="muted">Penerima</div>
                        <div>: {{ $penerima }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="head">Keterangan</div>
                <div class="body">
                    <div class="kv-sm">
                        <div class="muted">Uraian</div><div>: {{ $transaction->description ?? '-' }}</div>

                        <div class="muted">No. Ref</div><div>: {{ $transaction->reference_number ?? '-' }}</div>

                        <div class="muted">Dibuat</div>
                        <div>: {{ $transaction->created_by_name ?? (auth()->user()->name ?? '-') }} 
                            ({{ optional($transaction->created_at)->format('d/m/Y') }})
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Amount --}}
        <div style="display:grid; grid-template-columns:200px 1fr; gap:12px; margin-top:12px;">
            <div class="amount-split-box-num">
                <div style="font-size:10px; color:var(--muted); letter-spacing:.1em;">JUMLAH</div>
                <div style="font-size:20px; font-weight:900;">
                    Rp {{ number_format($transaction->amount,0,',','.') }}
                </div>
            </div>

            <div class="amount-split-box-words">
                <div style="font-style:italic; font-weight:600; color:#4b5563; font-size:13px;">
                    # {{ ucwords($__words) }} Rupiah #
                </div>
            </div>
        </div>

        {{-- Signature --}}
        <div class="signatures">
            <div class="sig">
                <div class="role">Disetujui</div>
                <div class="line"></div>
                <div class="name">(...........................)</div>
            </div>

            <div class="sig">
                <div class="role">Dibuat</div>
                <div class="line"></div>
                <div class="name">({{ $transaction->created_by_name ?? (auth()->user()->name ?? '..................') }})</div>
            </div>

            <div class="sig">
                <div class="role">Diterima</div>
                <div class="line"></div>
                <div class="name">(...........................)</div>
            </div>
        </div>

        <div class="footer">
            Dicetak: {{ now()->format('d/m/Y H:i') }} • {{ $company }}
        </div>

    </div>
</div>

@if(request()->boolean('print'))
<script>
    window.addEventListener('load', function(){
        window.print();
        window.onafterprint = () => window.close();
    });
</script>
@endif

</body>
</html>
