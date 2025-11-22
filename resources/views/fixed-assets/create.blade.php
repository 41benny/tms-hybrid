@extends('layouts.app')
@section('content')
<div class="container">
    <div class="mb-4 text-xl font-bold">Tambah Aset Tetap</div>
    <form method="post" action="{{ route('fixed-assets.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Kode</label>
                <input name="code" class="form-control" required>
            </div>
            <div class="col">
                <label class="form-label">Nama</label>
                <input name="name" class="form-control" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Tanggal Perolehan</label>
                <input type="date" name="acquisition_date" class="form-control" required>
            </div>
            <div class="col">
                <label class="form-label">Nilai Perolehan</label>
                <input type="number" step="0.01" name="acquisition_cost" class="form-control" required>
            </div>
            <div class="col">
                <label class="form-label">Umur (bulan)</label>
                <input type="number" name="useful_life_months" class="form-control" required>
            </div>
            <div class="col">
                <label class="form-label">Nilai Residu</label>
                <input type="number" step="0.01" name="residual_value" class="form-control" value="0">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Akun Aset</label>
                <select name="account_asset_id" class="form-select" required>
                    @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div class="col">
                <label class="form-label">Akun Akumulasi</label>
                <select name="account_accum_id" class="form-select" required>
                    @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div class="col">
                <label class="form-label">Akun Beban Depresiasi</label>
                <select name="account_expense_id" class="form-select" required>
                    @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('fixed-assets.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
