@extends('layouts.app', ['title' => 'Edit Shipment Leg'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <x-button :href="route('job-orders.show', $jobOrder)" variant="ghost" size="sm">
            ← Kembali
        </x-button>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Edit Shipment Leg #{{ $leg->leg_number }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Job: {{ $jobOrder->job_number }} • {{ $leg->leg_code }}</p>
        </div>
    </div>

    @php
        $vendorBills = \App\Models\Finance\VendorBill::query()
            ->whereHas('items', function($q) use ($leg) {
                $q->where('shipment_leg_id', $leg->id);
            })
            ->get();
        $totalGenerated = (float) $leg->vendorBillItems()->sum('subtotal');
    @endphp

    @if($vendorBills->count() > 0)
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">
                    ⚠️ Leg ini sudah punya {{ $vendorBills->count() }} Vendor Bill(s)
                </h3>
                <div class="text-xs text-yellow-700 dark:text-yellow-300 space-y-1">
                    @foreach($vendorBills as $vb)
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ route('vendor-bills.show', $vb) }}" target="_blank" class="hover:underline">
                            {{ $vb->vendor_bill_number }} ({{ $vb->status }})
                        </a>
                        <span class="font-medium">Rp {{ number_format($vb->total_amount, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                    <div class="pt-1 mt-2 border-t border-yellow-300 dark:border-yellow-700">
                        <strong>Total Billed: Rp {{ number_format($totalGenerated, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-2">
                    <strong>Penting:</strong> Jika edit biaya di leg ini, jangan lupa update vendor bill(s) di atas agar tetap sinkron!
                </p>
            </div>
        </div>
    </div>
    @endif

    <form method="post" action="{{ route('job-orders.legs.update', [$jobOrder, $leg]) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        {{-- Main Details --}}
        <x-card title="Main Details" subtitle="Detail transportasi">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-select 
                    name="cost_category" 
                    label="Cost Category"
                    :error="$errors->first('cost_category')"
                    :required="true"
                    id="cost_category"
                >
                    <option value="">-- Pilih Category --</option>
                    <option value="trucking" @selected(old('cost_category', $leg->cost_category)=='trucking')>Trucking (Own Fleet)</option>
                    <option value="vendor" @selected(old('cost_category', $leg->cost_category)=='vendor')>Vendor</option>
                    <option value="pelayaran" @selected(old('cost_category', $leg->cost_category)=='pelayaran')>Pelayaran (Sea Freight)</option>
                    <option value="asuransi" @selected(old('cost_category', $leg->cost_category)=='asuransi')>Asuransi (Insurance)</option>
                    <option value="pic" @selected(old('cost_category', $leg->cost_category)=='pic')>PIC (Fee/Insentif Perorangan)</option>
                </x-select>

                {{-- Vendor Field - Dinamis, tidak muncul untuk trucking --}}
                <div id="vendor_field">
                    <x-select 
                        name="vendor_id" 
                        label="Vendor"
                        :error="$errors->first('vendor_id')"
                        id="vendor_id"
                    >
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" @selected(old('vendor_id', $leg->vendor_id)==$v->id)>{{ $v->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                {{-- Trucking Fields (Own Fleet) --}}
                <div id="trucking_fields" class="hidden grid-cols-subgrid col-span-2 gap-6">
                    <x-select 
                        name="truck_id" 
                        label="Nopol Truck"
                        :error="$errors->first('truck_id')"
                        id="truck_id"
                    >
                        <option value="">-- Pilih Nopol Truck --</option>
                        @foreach($trucks as $t)
                            <option value="{{ $t->id }}" data-driver-id="{{ $t->driver_id }}" data-driver-name="{{ $t->driver?->name }}" @selected(old('truck_id', $leg->truck_id)==$t->id)>{{ $t->plate_number }}</option>
                        @endforeach
                    </x-select>

                    <x-input 
                        name="driver_name" 
                        label="Nama Supir" 
                        :value="old('driver_name', $leg->driver?->name)"
                        placeholder="Supir akan terisi otomatis"
                        id="driver_name"
                        readonly
                    />
                    <input type="hidden" name="driver_id" id="driver_id" value="{{ old('driver_id', $leg->driver_id) }}">
                </div>

                {{-- Vendor Fields - REMOVED (sudah ada vendor_id di atas yang wajib untuk semua kategori) --}}

                {{-- Pelayaran Fields --}}
                <div id="vessel_field" class="hidden">
                    <x-input 
                        name="vessel_name" 
                        label="Vessel Name" 
                        :value="old('vessel_name', $leg->vessel_name)"
                        :error="$errors->first('vessel_name')"
                        placeholder="e.g., KM Bahari"
                    />
                </div>

                {{-- Common Fields --}}

                <x-input 
                    name="load_date" 
                    type="date"
                    label="Load Date" 
                    :value="old('load_date', $leg->load_date->format('Y-m-d'))"
                    :error="$errors->first('load_date')"
                    :required="true"
                />

                <x-input 
                    name="unload_date" 
                    type="date"
                    label="Unload Date" 
                    :value="old('unload_date', $leg->unload_date->format('Y-m-d'))"
                    :error="$errors->first('unload_date')"
                    :required="true"
                />

                <x-input 
                    name="quantity" 
                    type="number"
                    step="0.01"
                    label="Quantity" 
                    :value="old('quantity', $leg->quantity)"
                    :error="$errors->first('quantity')"
                    :required="true"
                />

                <x-input 
                    name="serial_numbers" 
                    label="Serial Numbers" 
                    :value="old('serial_numbers', $leg->serial_numbers)"
                    :error="$errors->first('serial_numbers')"
                    placeholder="e.g., SN-001, SN-002"
                />

                <x-select 
                    name="status" 
                    label="Status"
                    :error="$errors->first('status')"
                    :required="true"
                    class="md:col-span-2"
                >
                    <option value="pending" @selected(old('status', $leg->status)=='pending')>Pending</option>
                    <option value="in_transit" @selected(old('status', $leg->status)=='in_transit')>In Transit</option>
                    <option value="delivered" @selected(old('status', $leg->status)=='delivered')>Delivered</option>
                    <option value="cancelled" @selected(old('status', $leg->status)=='cancelled')>Cancelled</option>
                </x-select>
            </div>
        </x-card>

        {{-- Main Cost Details --}}
        <x-card title="Main Cost Details" subtitle="Biaya utama untuk leg ini">
            {{-- Trucking Costs (Own Fleet) --}}
            <div id="trucking_costs" class="hidden grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Uang Jalan (IDR)</label>
                    <input 
                        type="text"
                        id="uang_jalan_display"
                        placeholder="500.000"
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    >
                    <input type="hidden" name="uang_jalan" id="uang_jalan_input" value="{{ old('uang_jalan', $leg->mainCost?->uang_jalan ?? 0) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">BBM (IDR)</label>
                    <input 
                        type="text"
                        id="bbm_display"
                        placeholder="300.000"
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    >
                    <input type="hidden" name="bbm" id="bbm_input" value="{{ old('bbm', $leg->mainCost?->bbm ?? 0) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tol (IDR)</label>
                    <input 
                        type="text"
                        id="toll_display"
                        placeholder="150.000"
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    >
                    <input type="hidden" name="toll" id="toll_input" value="{{ old('toll', $leg->mainCost?->toll ?? 0) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Other Costs (IDR)</label>
                    <input 
                        type="text"
                        id="other_costs_display"
                        placeholder="100.000"
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    >
                    <input type="hidden" name="other_costs" id="other_costs_input" value="{{ old('other_costs', $leg->mainCost?->other_costs ?? 0) }}">
                </div>
            </div>

            {{-- Vendor Costs --}}
            <div id="vendor_costs" class="hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Vendor Cost (IDR)</label>
                        <input 
                            type="text"
                            id="vendor_cost_display"
                            placeholder="3.000.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="vendor_cost" id="vendor_cost_input" value="{{ old('vendor_cost', $leg->mainCost?->vendor_cost ?? 0) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">PPN 11% (IDR)</label>
                        <input 
                            type="text"
                            id="ppn_display"
                            placeholder="330.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="ppn" id="ppn_input" value="{{ old('ppn', $leg->mainCost?->ppn ?? 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">PPN 11% dari vendor cost (bisa diedit)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">PPH 23 (IDR) - Dipotong</label>
                        <input 
                            type="text"
                            id="pph23_display"
                            placeholder="60.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="pph23" id="pph23_input" value="{{ old('pph23', $leg->mainCost?->pph23 ?? 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">PPH 23 2% dipotong dari vendor cost</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 rounded-lg p-5 border border-indigo-200 dark:border-indigo-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Jumlah Total:</span>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="vendor_total_display">Rp 0</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Vendor Cost + PPN - PPH 23</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pelayaran Costs --}}
            <div id="pelayaran_costs" class="hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-input 
                        name="shipping_line" 
                        label="Shipping Line" 
                        :value="old('shipping_line', $leg->mainCost?->shipping_line)"
                        placeholder="e.g., Meratus Line"
                    />
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Freight Cost (IDR)</label>
                        <input 
                            type="text"
                            id="freight_cost_display"
                            placeholder="4.000.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="freight_cost" id="freight_cost_input" value="{{ old('freight_cost', $leg->mainCost?->freight_cost ?? 0) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">PPN 11% (IDR)</label>
                        <input 
                            type="text"
                            id="ppn_pelayaran_display"
                            placeholder="440.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="ppn" id="ppn_pelayaran_input" value="{{ old('ppn', $leg->mainCost?->ppn ?? 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">PPN 11% dari freight cost (bisa diedit)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">PPH 23 (IDR) - Dipotong</label>
                        <input 
                            type="text"
                            id="pph23_pelayaran_display"
                            placeholder="80.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="pph23" id="pph23_pelayaran_input" value="{{ old('pph23', $leg->mainCost?->pph23 ?? 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">PPH 23 2% dipotong dari freight cost</p>
                    </div>
                    
                    <x-input 
                        name="container_no" 
                        label="Container No." 
                        :value="old('container_no', $leg->mainCost?->container_no)"
                        placeholder="e.g., MRKU-123456-7"
                        class="md:col-span-2"
                    />
                </div>
                
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 rounded-lg p-5 border border-indigo-200 dark:border-indigo-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Jumlah Total:</span>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="pelayaran_total_display">Rp 0</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Freight Cost + PPN - PPH 23</div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="asuransi_costs" class="hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        name="insurance_provider" 
                        label="Perusahaan Asuransi" 
                        :value="old('insurance_provider', $leg->mainCost?->insurance_provider)"
                        placeholder="e.g., PT Asuransi Jasa Raharja"
                    />

                    <x-input 
                        name="policy_number" 
                        label="Nomor Polis" 
                        :value="old('policy_number', $leg->mainCost?->policy_number)"
                        placeholder="e.g., POL-2024-001234"
                    />

                    <x-input 
                        name="insured_value" 
                        type="number"
                        step="0.01"
                        label="Nilai Pertanggungan (IDR)" 
                        :value="old('insured_value', $leg->mainCost?->insured_value ?? 0)"
                        placeholder="e.g., 5000000000"
                        id="insured_value_edit"
                    />

                    <x-input 
                        name="premium_rate" 
                        type="number"
                        step="0.01"
                        label="Rate Premi (%)" 
                        :value="old('premium_rate', $leg->mainCost?->premium_rate ?? 0)"
                        placeholder="e.g., 0.10"
                        id="premium_rate_edit"
                    />

                    <x-input 
                        name="admin_fee" 
                        type="number"
                        step="0.01"
                        label="Biaya Admin (IDR)" 
                        :value="old('admin_fee', $leg->mainCost?->admin_fee ?? 0)"
                        placeholder="e.g., 50000"
                        id="admin_fee_edit"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Premi yang Dibayar (IDR) <span class="text-xs text-slate-500">(Auto)</span>
                        </label>
                        <input 
                            type="text"
                            id="premium_cost_display_edit"
                            readonly
                            value="Rp {{ number_format($leg->mainCost?->premium_cost ?? 0, 0, ',', '.') }}"
                            class="w-full rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 font-medium"
                        >
                        <input type="hidden" name="premium_cost" id="premium_cost_edit" value="{{ old('premium_cost', $leg->mainCost?->premium_cost ?? 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">= (Nilai Pertanggungan × Rate%) + Biaya Admin</p>
                    </div>

                    <x-input 
                        name="billable_rate" 
                        type="number"
                        step="0.01"
                        label="Rate untuk Customer (%)" 
                        :value="old('billable_rate', $leg->mainCost?->billable_rate ?? 0)"
                        placeholder="e.g., 0.15"
                        id="billable_rate_edit"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Premi yang Ditagihkan ke Customer (IDR) <span class="text-xs text-slate-500">(Auto)</span>
                        </label>
                        <input 
                            type="text"
                            id="premium_billable_display_edit"
                            readonly
                            value="Rp {{ number_format($leg->mainCost?->premium_billable ?? 0, 0, ',', '.') }}"
                            class="w-full rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 font-medium"
                        >
                        <input type="hidden" name="premium_billable" id="premium_billable_edit" value="{{ old('premium_billable', $leg->mainCost?->premium_billable ?? 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">= Nilai Pertanggungan × Rate Customer (tanpa by admin)</p>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 rounded-lg p-4 border border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-green-600 dark:text-green-400 mb-1">Margin Premi</div>
                            <div class="text-sm text-green-700 dark:text-green-300" id="margin_info_edit">Rp 0</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="margin_percentage_edit">0%</div>
                            <div class="text-xs text-green-600 dark:text-green-400">Margin</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PIC Costs (Fee/Insentif Perorangan) --}}
            <div id="pic_costs" class="hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select 
                        name="cost_type" 
                        label="Tipe Biaya"
                        :error="$errors->first('cost_type')"
                    >
                        <option value="">-- Pilih Tipe --</option>
                        <option value="fee" @selected(old('cost_type', $leg->mainCost?->cost_type)=='fee')>Fee</option>
                        <option value="insentif" @selected(old('cost_type', $leg->mainCost?->cost_type)=='insentif')>Insentif</option>
                        <option value="upah_operator" @selected(old('cost_type', $leg->mainCost?->cost_type)=='upah_operator')>Upah Operator</option>
                        <option value="lainnya" @selected(old('cost_type', $leg->mainCost?->cost_type)=='lainnya')>Lainnya</option>
                    </x-select>

                    <x-input 
                        name="pic_name" 
                        label="Nama PIC" 
                        :value="old('pic_name', $leg->mainCost?->pic_name)"
                        :error="$errors->first('pic_name')"
                        placeholder="e.g., Budi Santoso"
                    />

                    <x-input 
                        name="pic_phone" 
                        label="No HP PIC" 
                        :value="old('pic_phone', $leg->mainCost?->pic_phone)"
                        :error="$errors->first('pic_phone')"
                        placeholder="e.g., 08123456789"
                    />

                    <x-input 
                        name="pic_amount" 
                        type="number"
                        step="0.01"
                        label="Jumlah Pembayaran (IDR)" 
                        :value="old('pic_amount', $leg->mainCost?->pic_amount ?? 0)"
                        :error="$errors->first('pic_amount')"
                        placeholder="e.g., 500000"
                    />

                    <div class="md:col-span-2">
                        <x-textarea 
                            name="pic_notes" 
                            label="Catatan"
                            :error="$errors->first('pic_notes')"
                            placeholder="Catatan tambahan (opsional)..."
                            :rows="2"
                        >{{ old('pic_notes', $leg->mainCost?->pic_notes) }}</x-textarea>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Actions --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('job-orders.show', $jobOrder)" variant="outline">
                    Cancel
                </x-button>
                <x-button type="submit" variant="primary">
                    💾 Update Leg
                </x-button>
            </div>
        </x-card>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const costCategory = document.getElementById('cost_category');
    const vendorField = document.getElementById('vendor_field');
    const vendorSelect = document.getElementById('vendor_id');
    const vesselField = document.getElementById('vessel_field');
    const vendorCosts = document.getElementById('vendor_costs');
    const truckingCosts = document.getElementById('trucking_costs');
    const pelayaranCosts = document.getElementById('pelayaran_costs');
    const asuransiCosts = document.getElementById('asuransi_costs');
    const picCosts = document.getElementById('pic_costs');
    const truckSelect = document.getElementById('truck_id');
    const driverName = document.getElementById('driver_name');
    const driverId = document.getElementById('driver_id');

    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    function parseNumber(str) {
        return parseFloat(str.replace(/\./g, '')) || 0;
    }
    
    // Setup formatted input
    function setupFormattedInput(displayId, hiddenId, onInputCallback = null) {
        const displayInput = document.getElementById(displayId);
        const hiddenInput = document.getElementById(hiddenId);
        
        if (!displayInput || !hiddenInput) return;
        
        displayInput.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');
            
            if (value) {
                this.value = formatNumber(value);
                hiddenInput.value = value;
            } else {
                this.value = '';
                hiddenInput.value = '0';
            }
            
            if (onInputCallback) onInputCallback();
        });
        
        // Initialize with existing value
        if (hiddenInput.value && parseFloat(hiddenInput.value) > 0) {
            displayInput.value = formatNumber(hiddenInput.value);
        }
    }

    const truckingFields = document.getElementById('trucking_fields');
    
    function updateFormFields() {
        const category = costCategory.value;
        
        // Hide all dynamic fields first
        truckingFields.classList.add('hidden');
        truckingFields.classList.remove('grid');
        vesselField.classList.add('hidden');
        vendorField.classList.add('hidden');
        vendorCosts.classList.add('hidden');
        truckingCosts.classList.add('hidden');
        pelayaranCosts.classList.add('hidden');
        asuransiCosts.classList.add('hidden');
        picCosts.classList.add('hidden');

        // Show relevant fields based on cost category
        if (category === 'trucking') {
            // Own Fleet - show truck, driver, uang jalan, BBM, tol (TIDAK ADA vendor field)
            truckingFields.classList.remove('hidden');
            truckingFields.classList.add('grid');
            truckingCosts.classList.remove('hidden');
            // Clear vendor selection when trucking is selected
            if (vendorSelect) vendorSelect.value = '';
        } else if (category === 'vendor') {
            // Vendor - show vendor field, vendor cost, PPN, PPH23
            vendorField.classList.remove('hidden');
            vendorCosts.classList.remove('hidden');
        } else if (category === 'pelayaran') {
            // Sea Freight - show vendor field, vessel, shipping line, freight cost, container
            vendorField.classList.remove('hidden');
            vesselField.classList.remove('hidden');
            pelayaranCosts.classList.remove('hidden');
        } else if (category === 'asuransi') {
            // Insurance - show vendor field, insurance form
            vendorField.classList.remove('hidden');
            asuransiCosts.classList.remove('hidden');
        } else if (category === 'pic') {
            // PIC - show vendor field, PIC form
            vendorField.classList.remove('hidden');
            picCosts.classList.remove('hidden');
        }
    }

    costCategory.addEventListener('change', updateFormFields);

    // Auto-fill driver when truck is selected
    if (truckSelect) {
        truckSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option && option.value) {
                const driverIdVal = option.getAttribute('data-driver-id');
                const driverNameVal = option.getAttribute('data-driver-name');
                
                if (driverName) driverName.value = driverNameVal || '';
                if (driverId) driverId.value = driverIdVal || '';
            } else {
                if (driverName) driverName.value = '';
                if (driverId) driverId.value = '';
            }
        });
    }

    // Setup formatted inputs for trucking
    setupFormattedInput('uang_jalan_display', 'uang_jalan_input');
    setupFormattedInput('bbm_display', 'bbm_input');
    setupFormattedInput('toll_display', 'toll_input');
    setupFormattedInput('other_costs_display', 'other_costs_input');
    
    // Vendor costs auto-calculation
    const vendorCostInput = document.getElementById('vendor_cost_input');
    const ppnInput = document.getElementById('ppn_input');
    const pph23Input = document.getElementById('pph23_input');
    const totalDisplay = document.getElementById('vendor_total_display');
    
    function calculateVendorTax() {
        const vendorCost = parseFloat(vendorCostInput.value) || 0;
        
        // Auto-calculate PPN 11% (bisa di-override manual)
        if (!ppnInput.dataset.manuallyEdited) {
            const ppn = Math.round(vendorCost * 0.11);
            ppnInput.value = ppn;
            document.getElementById('ppn_display').value = formatNumber(ppn);
        }
        
        // Auto-calculate PPH 23 2% (bisa di-override manual)
        if (!pph23Input.dataset.manuallyEdited) {
            const pph23 = Math.round(vendorCost * 0.02);
            pph23Input.value = pph23;
            document.getElementById('pph23_display').value = formatNumber(pph23);
        }
        
        updateVendorTotal();
    }
    
    function updateVendorTotal() {
        const vendorCost = parseFloat(vendorCostInput.value) || 0;
        const ppn = parseFloat(ppnInput.value) || 0;
        const pph23 = parseFloat(pph23Input.value) || 0;
        const total = vendorCost + ppn - pph23;
        
        totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
    
    setupFormattedInput('vendor_cost_display', 'vendor_cost_input', calculateVendorTax);
    setupFormattedInput('ppn_display', 'ppn_input', function() {
        ppnInput.dataset.manuallyEdited = 'true';
        updateVendorTotal();
    });
    setupFormattedInput('pph23_display', 'pph23_input', function() {
        pph23Input.dataset.manuallyEdited = 'true';
        updateVendorTotal();
    });
    
    // Pelayaran costs auto-calculation
    const freightCostInput = document.getElementById('freight_cost_input');
    const ppnPelayaranInput = document.getElementById('ppn_pelayaran_input');
    const pph23PelayaranInput = document.getElementById('pph23_pelayaran_input');
    const pelayaranTotalDisplay = document.getElementById('pelayaran_total_display');
    
    function calculatePelayaranTax() {
        const freightCost = parseFloat(freightCostInput.value) || 0;
        
        if (!ppnPelayaranInput.dataset.manuallyEdited) {
            const ppn = Math.round(freightCost * 0.11);
            ppnPelayaranInput.value = ppn;
            document.getElementById('ppn_pelayaran_display').value = formatNumber(ppn);
        }
        
        if (!pph23PelayaranInput.dataset.manuallyEdited) {
            const pph23 = Math.round(freightCost * 0.02);
            pph23PelayaranInput.value = pph23;
            document.getElementById('pph23_pelayaran_display').value = formatNumber(pph23);
        }
        
        updatePelayaranTotal();
    }
    
    function updatePelayaranTotal() {
        const freightCost = parseFloat(freightCostInput.value) || 0;
        const ppn = parseFloat(ppnPelayaranInput.value) || 0;
        const pph23 = parseFloat(pph23PelayaranInput.value) || 0;
        const total = freightCost + ppn - pph23;
        
        pelayaranTotalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
    
    setupFormattedInput('freight_cost_display', 'freight_cost_input', calculatePelayaranTax);
    setupFormattedInput('ppn_pelayaran_display', 'ppn_pelayaran_input', function() {
        ppnPelayaranInput.dataset.manuallyEdited = 'true';
        updatePelayaranTotal();
    });
    setupFormattedInput('pph23_pelayaran_display', 'pph23_pelayaran_input', function() {
        pph23PelayaranInput.dataset.manuallyEdited = 'true';
        updatePelayaranTotal();
    });

    updateFormFields();
    
    // Trigger calculations on page load
    if (vendorCostInput && parseFloat(vendorCostInput.value) > 0) {
        calculateVendorTax();
    }
    if (freightCostInput && parseFloat(freightCostInput.value) > 0) {
        calculatePelayaranTax();
    }
    
    // Auto-calculate Insurance Premium (Edit mode)
    const insuredValueEdit = document.getElementById('insured_value_edit');
    const premiumRateEdit = document.getElementById('premium_rate_edit');
    const adminFeeEdit = document.getElementById('admin_fee_edit');
    const billableRateEdit = document.getElementById('billable_rate_edit');
    const premiumCostEdit = document.getElementById('premium_cost_edit');
    const premiumCostDisplayEdit = document.getElementById('premium_cost_display_edit');
    const premiumBillableEdit = document.getElementById('premium_billable_edit');
    const premiumBillableDisplayEdit = document.getElementById('premium_billable_display_edit');
    const marginInfoEdit = document.getElementById('margin_info_edit');
    const marginPercentageEdit = document.getElementById('margin_percentage_edit');
    
    function calculateInsurancePremiumEdit() {
        const insured = parseFloat(insuredValueEdit?.value) || 0;
        const rate = parseFloat(premiumRateEdit?.value) || 0;
        const admin = parseFloat(adminFeeEdit?.value) || 0;
        const billRate = parseFloat(billableRateEdit?.value) || 0;
        
        // Calculate Premium Cost = (Insured Value × Rate%) + Admin Fee
        const cost = (insured * rate / 100) + admin;
        if (premiumCostEdit) premiumCostEdit.value = cost.toFixed(2);
        if (premiumCostDisplayEdit) premiumCostDisplayEdit.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(cost);
        
        // Calculate Premium Billable = Insured Value × Billable Rate% (without admin)
        const billable = insured * billRate / 100;
        if (premiumBillableEdit) premiumBillableEdit.value = billable.toFixed(2);
        if (premiumBillableDisplayEdit) premiumBillableDisplayEdit.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(billable);
        
        // Calculate Margin
        const margin = billable - cost;
        const marginPct = cost > 0 ? (margin / cost * 100) : 0;
        
        if (marginInfoEdit) {
            marginInfoEdit.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(margin);
        }
        if (marginPercentageEdit) {
            marginPercentageEdit.textContent = marginPct.toFixed(2) + '%';
        }
    }
    
    // Add event listeners for insurance fields
    if (insuredValueEdit) insuredValueEdit.addEventListener('input', calculateInsurancePremiumEdit);
    if (premiumRateEdit) premiumRateEdit.addEventListener('input', calculateInsurancePremiumEdit);
    if (adminFeeEdit) adminFeeEdit.addEventListener('input', calculateInsurancePremiumEdit);
    if (billableRateEdit) billableRateEdit.addEventListener('input', calculateInsurancePremiumEdit);
    
    // Calculate on page load if editing insurance leg
    if (insuredValueEdit) {
        calculateInsurancePremiumEdit();
    }
});
</script>
@endsection


