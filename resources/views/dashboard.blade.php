@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Ringkasan">
            <div class="flex items-center gap-2">
                <x-badge variant="success">Jobs Selesai: 12</x-badge>
                <x-badge variant="warning">Jobs Berjalan: 5</x-badge>
                <x-badge variant="danger">Issue: 1</x-badge>
            </div>
        </x-card>
        <x-card title="Pendapatan Bulan Ini">
            <div class="text-2xl font-semibold">Rp 125.000.000</div>
        </x-card>
        <x-card title="Biaya Vendor Bulan Ini">
            <div class="text-2xl font-semibold">Rp 75.000.000</div>
        </x-card>
    </div>

    <div class="mt-4">
        <x-card title="Aktivitas Terbaru">
            <ul class="list-disc ml-5 space-y-1">
                <li>Invoice INV-001 diposting</li>
                <li>Vendor bill VBL-002 dibuat</li>
                <li>Transport TR-010 delivered</li>
            </ul>
        </x-card>
    </div>
@endsection

