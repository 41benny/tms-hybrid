@extends('layouts.app')
@section('content')
<div class="container">
    <div class="mb-3 text-xl font-bold">Detail Aset: {{ $asset->code }}</div>
    <div class="mb-3 p-3 border rounded bg-light">
        <strong>Nama:</strong> {{ $asset->name }}<br>
        <strong>Tgl Perolehan:</strong> {{ $asset->acquisition_date->format('Y-m-d') }}<br>
        <strong>Nilai Perolehan:</strong> {{ number_format($asset->acquisition_cost,0,',','.') }}<br>
        <strong>Nilai Residu:</strong> {{ number_format($asset->residual_value,0,',','.') }}<br>
        <strong>Umur (bulan):</strong> {{ $asset->useful_life_months }}<br>
        <strong>Status:</strong> {{ ucfirst($asset->status) }}<br>
        <strong>Akumulasi Depresiasi:</strong> {{ number_format($asset->accumulatedDepreciation(),0,',','.') }}<br>
        <strong>Nilai Buku:</strong> {{ number_format($asset->bookValue(),0,',','.') }}
    </div>

    @if($asset->status==='active')
    <form method="post" action="{{ route('fixed-assets.depreciate',$asset) }}" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-outline-success" onclick="return confirm('Posting depresiasi bulan ini?')">Post Depresiasi Bulan Ini</button>
    </form>
    <a href="{{ route('fixed-assets.dispose.form',$asset) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Disposal aset? Pastikan data sudah benar.')">Disposal</a>
    @endif
    <a href="{{ route('fixed-assets.edit',$asset) }}" class="btn btn-sm btn-outline-primary">Edit</a>
    <a href="{{ route('fixed-assets.index') }}" class="btn btn-sm btn-secondary">Kembali</a>

    <hr>
    <h4>Riwayat Depresiasi</h4>
    <table class="table table-sm">
        <thead><tr><th>Periode</th><th>Jumlah</th><th>Journal ID</th></tr></thead>
        <tbody>
        @foreach($asset->depreciations as $d)
            <tr><td>{{ $d->period_ym }}</td><td>{{ number_format($d->amount,0,',','.') }}</td><td>{{ $d->posted_journal_id }}</td></tr>
        @endforeach
        </tbody>
    </table>

    <h4>Disposal</h4>
    @if($asset->disposals->count())
        <table class="table table-sm">
            <thead><tr><th>Tanggal</th><th>Proceeds</th><th>Journal</th></tr></thead>
            <tbody>@foreach($asset->disposals as $di)<tr><td>{{ $di->disposal_date }}</td><td>{{ number_format($di->proceed_amount,0,',','.') }}</td><td>{{ $di->posted_journal_id }}</td></tr>@endforeach</tbody>
        </table>
    @else
        <p class="text-muted">Belum ada disposal.</p>
    @endif
</div>
@endsection
