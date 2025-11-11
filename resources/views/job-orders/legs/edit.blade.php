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
                    <option value="trucking" @selected(old('cost_category', $leg->cost_category)=='trucking')>Trucking</option>
                    <option value="vendor" @selected(old('cost_category', $leg->cost_category)=='vendor')>Vendor</option>
                    <option value="pelayaran" @selected(old('cost_category', $leg->cost_category)=='pelayaran')>Pelayaran (Sea Freight)</option>
                    <option value="asuransi" @selected(old('cost_category', $leg->cost_category)=='asuransi')>Asuransi (Insurance)</option>
                </x-select>

                <x-select 
                    name="executor_type" 
                    label="Executor"
                    :error="$errors->first('executor_type')"
                    :required="true"
                    id="executor_type"
                >
                    <option value="">-- Pilih Executor --</option>
                    <option value="own_fleet" @selected(old('executor_type', $leg->executor_type)=='own_fleet')>Armada Sendiri</option>
                    <option value="vendor" @selected(old('executor_type', $leg->executor_type)=='vendor')>Vendor</option>
                </x-select>

                <div id="vendor_field" class="hidden">
                    <x-select 
                        name="vendor_id" 
                        label="Vendor"
                        :error="$errors->first('vendor_id')"
                    >
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" @selected(old('vendor_id', $leg->vendor_id)==$v->id)>{{ $v->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div id="truck_field" class="hidden">
                    <x-select 
                        name="truck_id" 
                        label="Nopol Truck"
                        :error="$errors->first('truck_id')"
                        id="truck_id"
                    >
                        <option value="">-- Pilih Nopol Truck --</option>
                        @foreach($trucks as $t)
                            <option value="{{ $t->id }}" @selected(old('truck_id', $leg->truck_id)==$t->id)>{{ $t->plate_number }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div id="driver_field" class="hidden">
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

                <div id="vessel_field" class="hidden md:col-span-2">
                    <x-input 
                        name="vessel_name" 
                        label="Nama Kapal/Pesawat" 
                        :value="old('vessel_name', $leg->vessel_name)"
                        :error="$errors->first('vessel_name')"
                        placeholder="Contoh: KM Bahari"
                    />
                </div>

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
            <div id="vendor_costs" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="vendor_cost" 
                    type="number"
                    step="0.01"
                    label="Vendor Cost (IDR)" 
                    :value="old('vendor_cost', $leg->mainCost?->vendor_cost ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="ppn" 
                    type="number"
                    step="0.01"
                    label="PPN (IDR)" 
                    :value="old('ppn', $leg->mainCost?->ppn ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="pph23" 
                    type="number"
                    step="0.01"
                    label="PPH 23 (IDR)" 
                    :value="old('pph23', $leg->mainCost?->pph23 ?? 0)"
                    placeholder="0"
                    class="md:col-span-2"
                />
            </div>

            <div id="trucking_costs" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="uang_jalan" 
                    type="number"
                    step="0.01"
                    label="Uang Jalan (IDR)" 
                    :value="old('uang_jalan', $leg->mainCost?->uang_jalan ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="bbm" 
                    type="number"
                    step="0.01"
                    label="BBM (IDR)" 
                    :value="old('bbm', $leg->mainCost?->bbm ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="toll" 
                    type="number"
                    step="0.01"
                    label="Tol (IDR)" 
                    :value="old('toll', $leg->mainCost?->toll ?? 0)"
                    placeholder="0"
                />
            </div>

            <div id="sea_freight_costs" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="shipping_line" 
                    label="Shipping Line" 
                    :value="old('shipping_line', $leg->mainCost?->shipping_line)"
                    placeholder="e.g., Meratus Line"
                />
                <x-input 
                    name="freight_cost" 
                    type="number"
                    step="0.01"
                    label="Freight Cost (IDR)" 
                    :value="old('freight_cost', $leg->mainCost?->freight_cost ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="container_no" 
                    label="Container No." 
                    :value="old('container_no', $leg->mainCost?->container_no)"
                    placeholder="e.g., MRKU-123456-7"
                    class="md:col-span-2"
                />
            </div>

            <div id="pelayaran_costs" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input 
                    name="shipping_line" 
                    label="Shipping Line" 
                    :value="old('shipping_line', $leg->mainCost?->shipping_line)"
                    placeholder="e.g., Meratus Line"
                />
                <x-input 
                    name="freight_cost" 
                    type="number"
                    step="0.01"
                    label="Freight Cost (IDR)" 
                    :value="old('freight_cost', $leg->mainCost?->freight_cost ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="ppn" 
                    type="number"
                    step="0.01"
                    label="PPN (IDR)" 
                    :value="old('ppn', $leg->mainCost?->ppn ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="pph23" 
                    type="number"
                    step="0.01"
                    label="PPH 23 (IDR)" 
                    :value="old('pph23', $leg->mainCost?->pph23 ?? 0)"
                    placeholder="0"
                />
                <x-input 
                    name="container_no" 
                    label="Container No." 
                    :value="old('container_no', $leg->mainCost?->container_no)"
                    placeholder="e.g., MRKU-123456-7"
                    class="md:col-span-2"
                />
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
    const executorType = document.getElementById('executor_type');
    const vendorField = document.getElementById('vendor_field');
    const truckField = document.getElementById('truck_field');
    const driverField = document.getElementById('driver_field');
    const vesselField = document.getElementById('vessel_field');
    const vendorCosts = document.getElementById('vendor_costs');
    const truckingCosts = document.getElementById('trucking_costs');
    const pelayaranCosts = document.getElementById('pelayaran_costs');
    const asuransiCosts = document.getElementById('asuransi_costs');
    const truckSelect = document.getElementById('truck_id');
    const driverName = document.getElementById('driver_name');
    const driverId = document.getElementById('driver_id');

    function updateFormFields() {
        const category = costCategory.value;
        const executor = executorType.value;
        vendorField.classList.add('hidden');
        truckField.classList.add('hidden');
        driverField.classList.add('hidden');
        vesselField.classList.add('hidden');
        vendorCosts.classList.add('hidden');
        truckingCosts.classList.add('hidden');
        pelayaranCosts.classList.add('hidden');
        asuransiCosts.classList.add('hidden');

        if (category === 'trucking') {
            if (executor === 'own_fleet') {
                truckField.classList.remove('hidden');
                driverField.classList.remove('hidden');
                truckingCosts.classList.remove('hidden');
            } else if (executor === 'vendor') {
                vendorField.classList.remove('hidden');
                vendorCosts.classList.remove('hidden');
            }
        } else if (category === 'vendor') {
            vendorField.classList.remove('hidden');
            vendorCosts.classList.remove('hidden');
        } else if (category === 'pelayaran') {
            vesselField.classList.remove('hidden');
            pelayaranCosts.classList.remove('hidden');
            if (executor === 'vendor') {
                vendorField.classList.remove('hidden');
            }
        } else if (category === 'asuransi') {
            asuransiCosts.classList.remove('hidden');
        }
    }

    costCategory.addEventListener('change', updateFormFields);
    executorType.addEventListener('change', updateFormFields);

    if (truckSelect) {
        truckSelect.addEventListener('change', function() {
            if (this.value) {
                fetch(`{{ route('api.truck-driver') }}?truck_id=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        driverName.value = data.driver_name || '';
                        driverId.value = data.driver_id || '';
                    });
            } else {
                driverName.value = '';
                driverId.value = '';
            }
        });
    }

    updateFormFields();
    
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


