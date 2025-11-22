@extends('layouts.app')
@section('content')
<div class="container">
    <div class="mb-4 text-xl font-bold">Edit Aset: {{ $asset->code }}</div>
    <form method="post" action="{{ route('fixed-assets.update', $asset) }}">
        @csrf
        @method('PUT')
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Nama</label>
                <input name="name" class="form-control" value="{{ $asset->name }}" required>
            </div>
            <div class="col">
                <label class="form-label">Tanggal Perolehan</label>
                <input type="date" name="acquisition_date" class="form-control" value="{{ $asset->acquisition_date->format('Y-m-d') }}" required>
            </div>
            <div class="col">
                <label class="form-label">Nilai Perolehan</label>
                <input type="number" step="0.01" name="acquisition_cost" class="form-control" value="{{ $asset->acquisition_cost }}" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Umur (bulan)</label>
                <input type="number" name="useful_life_months" class="form-control" value="{{ $asset->useful_life_months }}" required>
            </div>
            <div class="col">
                <label class="form-label">Nilai Residu</label>
                <input type="number" step="0.01" name="residual_value" class="form-control" value="{{ $asset->residual_value }}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Akun Aset</label>
                <select name="account_asset_id" class="form-select" required>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected($a->id==$asset->account_asset_id)>{{ $a->code }} - {{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div class="col">
                <label class="form-label">Akun Akumulasi</label>
                <select name="account_accum_id" class="form-select" required>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected($a->id==$asset->account_accum_id)>{{ $a->code }} - {{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div class="col">
                <label class="form-label">Akun Beban</label>
                <select name="account_expense_id" class="form-select" required>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected($a->id==$asset->account_expense_id)>{{ $a->code }} - {{ $a->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('fixed-assets.show', $asset) }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
