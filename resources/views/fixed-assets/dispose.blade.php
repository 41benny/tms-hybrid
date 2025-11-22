@extends('layouts.app')
@section('content')
<div class="container">
    <div class="mb-4 text-xl font-bold">Disposal Aset: {{ $asset->code }}</div>
    <div class="alert alert-warning">Pastikan perhitungan proceed dan akun laba/rugi sudah benar. Tindakan ini permanen.</div>
    <form method="post" action="{{ route('fixed-assets.dispose',$asset) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tanggal Disposal</label>
            <input type="date" name="disposal_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Proceeds (Jumlah diterima)</label>
            <input type="number" step="0.01" name="proceed_amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Akun Cash (Kode COA)</label>
            <input type="text" name="cash_account_code" class="form-control" placeholder="1110 atau 1120" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Akun Gain/Loss (Kode COA)</label>
            <input type="text" name="gain_loss_account_code" class="form-control" placeholder="7110 / 7200 dsb" required>
        </div>
        <button class="btn btn-danger" onclick="return confirm('Proses disposal?')">Proses Disposal</button>
        <a href="{{ route('fixed-assets.show',$asset) }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
