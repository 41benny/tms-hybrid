@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4 text-xl font-bold">Daftar Aset Tetap</div>
    <a href="{{ route('fixed-assets.create') }}" class="btn btn-primary mb-3">Tambah Aset Tetap</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tanggal Perolehan</th>
                <th>Nilai Perolehan</th>
                <th>Umur (bulan)</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
            <tr>
                <td>{{ $asset->code }}</td>
                <td>{{ $asset->name }}</td>
                <td>{{ $asset->acquisition_date->format('Y-m-d') }}</td>
                <td>{{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                <td>{{ $asset->useful_life_months }}</td>
                <td>{{ ucfirst($asset->status) }}</td>
                <td>
                    <a href="{{ route('fixed-assets.show', $asset) }}" class="btn btn-info btn-sm">Detail</a>
                    <a href="{{ route('fixed-assets.edit', $asset) }}" class="btn btn-warning btn-sm">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
