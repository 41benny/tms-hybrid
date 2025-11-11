@extends('layouts.app', ['title' => 'Add Shipment Leg'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <x-button :href="route('job-orders.show', $jobOrder)" variant="ghost" size="sm">
            ← Kembali
        </x-button>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Add Shipment Leg</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">For Job: {{ $jobOrder->job_number }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('job-orders.legs.store', $jobOrder) }}" class="space-y-6" id="legForm">
        @csrf
        
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
                    <option value="trucking" @selected(old('cost_category')=='trucking')>Trucking (Own Fleet)</option>
                    <option value="vendor" @selected(old('cost_category')=='vendor')>Vendor</option>
                    <option value="pelayaran" @selected(old('cost_category')=='pelayaran')>Pelayaran (Sea Freight)</option>
                    <option value="asuransi" @selected(old('cost_category')=='asuransi')>Asuransi (Insurance)</option>
                </x-select>

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
                            <option value="{{ $t->id }}" data-driver-id="{{ $t->driver_id }}" data-driver-name="{{ $t->driver?->name }}">{{ $t->plate_number }}</option>
                        @endforeach
                    </x-select>

                    <x-input 
                        name="driver_name" 
                        label="Nama Supir" 
                        :value="old('driver_name')"
                        placeholder="Supir akan terisi otomatis"
                        id="driver_name"
                        readonly
                    />
                    <input type="hidden" name="driver_id" id="driver_id">
                </div>

                {{-- Vendor Fields --}}
                <div id="vendor_field" class="hidden">
                    <x-select 
                        name="vendor_id" 
                        label="Vendor"
                        :error="$errors->first('vendor_id')"
                    >
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                {{-- Pelayaran Fields --}}
                <div id="vessel_field" class="hidden">
                    <x-input 
                        name="vessel_name" 
                        label="Vessel Name" 
                        :value="old('vessel_name')"
                        :error="$errors->first('vessel_name')"
                        placeholder="e.g., KM Bahari"
                    />
                </div>

                {{-- Common Fields --}}
                <x-input 
                    name="load_date" 
                    type="date"
                    label="Load Date" 
                    :value="old('load_date', date('Y-m-d'))"
                    :error="$errors->first('load_date')"
                    :required="true"
                />

                <x-input 
                    name="unload_date" 
                    type="date"
                    label="Unload Date" 
                    :value="old('unload_date', date('Y-m-d'))"
                    :error="$errors->first('unload_date')"
                    :required="true"
                />

                <x-input 
                    name="quantity" 
                    type="number"
                    step="0.01"
                    label="Quantity" 
                    :value="old('quantity', 1)"
                    :error="$errors->first('quantity')"
                    :required="true"
                />

                <x-input 
                    name="serial_numbers" 
                    label="Serial Numbers" 
                    :value="old('serial_numbers')"
                    :error="$errors->first('serial_numbers')"
                    placeholder="e.g., SN-001, SN-002"
                />
            </div>
        </x-card>

        {{-- Main Cost Details --}}
        <x-card title="Main Cost Details" subtitle="Biaya utama untuk leg ini">
            {{-- Trucking Costs (Own Fleet) --}}
            <div id="trucking_costs" class="hidden grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-input 
                    name="uang_jalan" 
                    type="number"
                    step="0.01"
                    label="Uang Jalan (IDR)" 
                    :value="old('uang_jalan', 0)"
                    placeholder="500000"
                />

                <x-input 
                    name="bbm" 
                    type="number"
                    step="0.01"
                    label="BBM (IDR)" 
                    :value="old('bbm', 0)"
                    placeholder="300000"
                />

                <x-input 
                    name="toll" 
                    type="number"
                    step="0.01"
                    label="Tol (IDR)" 
                    :value="old('toll', 0)"
                    placeholder="150000"
                />
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
                        <input type="hidden" name="vendor_cost" id="vendor_cost_input" value="{{ old('vendor_cost', 0) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">PPN 11% (IDR)</label>
                        <input 
                            type="text"
                            id="ppn_display"
                            placeholder="330.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="ppn" id="ppn_input" value="{{ old('ppn', 0) }}">
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
                        <input type="hidden" name="pph23" id="pph23_input" value="{{ old('pph23', 0) }}">
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
                        :value="old('shipping_line')"
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
                        <input type="hidden" name="freight_cost" id="freight_cost_input" value="{{ old('freight_cost', 0) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">PPN 11% (IDR)</label>
                        <input 
                            type="text"
                            id="ppn_pelayaran_display"
                            placeholder="440.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="ppn" id="ppn_pelayaran_input" value="{{ old('ppn', 0) }}">
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
                        <input type="hidden" name="pph23" id="pph23_pelayaran_input" value="{{ old('pph23', 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">PPH 23 2% dipotong dari freight cost</p>
                    </div>
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

            {{-- Insurance Costs --}}
            <div id="asuransi_costs" class="hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        name="insurance_provider" 
                        label="Perusahaan Asuransi" 
                        :value="old('insurance_provider')"
                        placeholder="e.g., PT Asuransi Jasa Raharja"
                    />

                    <x-input 
                        name="policy_number" 
                        label="Nomor Polis" 
                        :value="old('policy_number')"
                        placeholder="e.g., POL-2024-001234"
                    />

                    <x-input 
                        name="insured_value" 
                        type="number"
                        step="0.01"
                        label="Nilai Pertanggungan (IDR)" 
                        :value="old('insured_value', 0)"
                        placeholder="e.g., 5000000000"
                        id="insured_value"
                    />

                    <x-input 
                        name="premium_rate" 
                        type="number"
                        step="0.01"
                        label="Rate Premi (%)" 
                        :value="old('premium_rate', 0)"
                        placeholder="e.g., 0.10"
                        id="premium_rate"
                    />

                    <x-input 
                        name="admin_fee" 
                        type="number"
                        step="0.01"
                        label="Biaya Admin (IDR)" 
                        :value="old('admin_fee', 0)"
                        placeholder="e.g., 50000"
                        id="admin_fee"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Premi yang Dibayar (IDR) <span class="text-xs text-slate-500">(Auto)</span>
                        </label>
                        <input 
                            type="text"
                            id="premium_cost_display"
                            readonly
                            class="w-full rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 font-medium"
                        >
                        <input type="hidden" name="premium_cost" id="premium_cost">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">= (Nilai Pertanggungan × Rate%) + Biaya Admin</p>
                    </div>

                    <x-input 
                        name="billable_rate" 
                        type="number"
                        step="0.01"
                        label="Rate untuk Customer (%)" 
                        :value="old('billable_rate', 0)"
                        placeholder="e.g., 0.15"
                        id="billable_rate"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Premi yang Ditagihkan ke Customer (IDR) <span class="text-xs text-slate-500">(Auto)</span>
                        </label>
                        <input 
                            type="text"
                            id="premium_billable_display"
                            readonly
                            class="w-full rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 font-medium"
                        >
                        <input type="hidden" name="premium_billable" id="premium_billable">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">= Nilai Pertanggungan × Rate Customer (tanpa by admin)</p>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 rounded-lg p-4 border border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-green-600 dark:text-green-400 mb-1">Margin Premi</div>
                            <div class="text-sm text-green-700 dark:text-green-300" id="margin_info">Rp 0</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="margin_percentage">0%</div>
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
                <x-button type="submit" variant="primary" id="submitBtn">
                    💾 Save Leg
                </x-button>
            </div>
        </x-card>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const legForm = document.getElementById('legForm');
    const submitBtn = document.getElementById('submitBtn');
    let isSubmitting = false;
    
    // Prevent double submit
    legForm.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        
        isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Saving...';
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    });
    
    const costCategory = document.getElementById('cost_category');
    
    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    function parseNumber(str) {
        return parseFloat(str.replace(/\./g, '')) || 0;
    }
    
    // Setup formatted input
    function setupFormattedInput(displayId, hiddenId) {
        const displayInput = document.getElementById(displayId);
        const hiddenInput = document.getElementById(hiddenId);
        
        if (!displayInput || !hiddenInput) return;
        
        displayInput.addEventListener('input', function() {
            let value = this.value.replace(/\./g, ''); // Remove dots
            value = value.replace(/[^\d]/g, ''); // Only digits
            
            if (value) {
                this.value = formatNumber(value);
                hiddenInput.value = value;
            } else {
                this.value = '';
                hiddenInput.value = '0';
            }
            
            // Trigger input event on hidden field
            hiddenInput.dispatchEvent(new Event('input'));
        });
        
        // Initialize with old value
        if (hiddenInput.value && hiddenInput.value != '0') {
            displayInput.value = formatNumber(hiddenInput.value);
        }
    }
    
    // Setup main cost formatted inputs (only vendor and freight)
    setupFormattedInput('vendor_cost_display', 'vendor_cost_input');
    setupFormattedInput('freight_cost_display', 'freight_cost_input');
    
    // Fields containers
    const truckingFields = document.getElementById('trucking_fields');
    const vendorField = document.getElementById('vendor_field');
    const vesselField = document.getElementById('vessel_field');
    
    // Cost containers
    const truckingCosts = document.getElementById('trucking_costs');
    const vendorCosts = document.getElementById('vendor_costs');
    const pelayaranCosts = document.getElementById('pelayaran_costs');
    const asuransiCosts = document.getElementById('asuransi_costs');
    
    // Truck & driver
    const truckSelect = document.getElementById('truck_id');
    const driverName = document.getElementById('driver_name');
    const driverId = document.getElementById('driver_id');

    // Handle cost category change
    function updateFields() {
        // Hide all fields first
        truckingFields.classList.add('hidden');
        vendorField.classList.add('hidden');
        vesselField.classList.add('hidden');
        
        truckingCosts.classList.add('hidden');
        vendorCosts.classList.add('hidden');
        pelayaranCosts.classList.add('hidden');
        asuransiCosts.classList.add('hidden');
        
        // Show relevant fields based on selection
        const value = costCategory.value;
        
        if (value === 'trucking') {
            // Own Fleet - show truck, driver, uang jalan, BBM, tol
            truckingFields.classList.remove('hidden');
            truckingFields.classList.add('grid');
            truckingCosts.classList.remove('hidden');
        } else if (value === 'vendor') {
            // Vendor - show vendor, vendor cost, PPN, PPH23
            vendorField.classList.remove('hidden');
            vendorCosts.classList.remove('hidden');
        } else if (value === 'pelayaran') {
            // Sea Freight - show vessel, shipping line, freight cost, container
            vesselField.classList.remove('hidden');
            pelayaranCosts.classList.remove('hidden');
        } else if (value === 'asuransi') {
            // Insurance - show insurance form
            asuransiCosts.classList.remove('hidden');
        }
    }

    costCategory.addEventListener('change', updateFields);

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

    // Trigger on page load
    updateFields();
    
    // Auto-calculate Insurance Premium
    const insuredValue = document.getElementById('insured_value');
    const premiumRate = document.getElementById('premium_rate');
    const adminFee = document.getElementById('admin_fee');
    const billableRate = document.getElementById('billable_rate');
    const premiumCost = document.getElementById('premium_cost');
    const premiumCostDisplay = document.getElementById('premium_cost_display');
    const premiumBillable = document.getElementById('premium_billable');
    const premiumBillableDisplay = document.getElementById('premium_billable_display');
    const marginInfo = document.getElementById('margin_info');
    const marginPercentage = document.getElementById('margin_percentage');
    
    function calculateInsurancePremium() {
        const insured = parseFloat(insuredValue?.value) || 0;
        const rate = parseFloat(premiumRate?.value) || 0;
        const admin = parseFloat(adminFee?.value) || 0;
        const billRate = parseFloat(billableRate?.value) || 0;
        
        // Calculate Premium Cost = (Insured Value × Rate%) + Admin Fee
        const cost = (insured * rate / 100) + admin;
        if (premiumCost) premiumCost.value = cost.toFixed(2);
        if (premiumCostDisplay) premiumCostDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(cost);
        
        // Calculate Premium Billable = Insured Value × Billable Rate% (without admin)
        const billable = insured * billRate / 100;
        if (premiumBillable) premiumBillable.value = billable.toFixed(2);
        if (premiumBillableDisplay) premiumBillableDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(billable);
        
        // Calculate Margin
        const margin = billable - cost;
        const marginPct = cost > 0 ? (margin / cost * 100) : 0;
        
        if (marginInfo) {
            marginInfo.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(margin);
        }
        if (marginPercentage) {
            marginPercentage.textContent = marginPct.toFixed(2) + '%';
        }
    }
    
    // Add event listeners for insurance fields
    if (insuredValue) insuredValue.addEventListener('input', calculateInsurancePremium);
    if (premiumRate) premiumRate.addEventListener('input', calculateInsurancePremium);
    if (adminFee) adminFee.addEventListener('input', calculateInsurancePremium);
    if (billableRate) billableRate.addEventListener('input', calculateInsurancePremium);
    
    // Auto-calculate PPN and PPH23 for Vendor costs
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
        const total = vendorCost + ppn - pph23; // PPH 23 dipotong (minus)
        
        totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
    
    if (vendorCostInput) {
        vendorCostInput.addEventListener('input', calculateVendorTax);
        
        // PPN and PPH inputs are now formatted, track via display fields
        const ppnDisplay = document.getElementById('ppn_display');
        const pph23Display = document.getElementById('pph23_display');
        
        let originalPpn = ppnInput.value;
        let originalPph23 = pph23Input.value;
        
        // Setup formatted inputs for PPN and PPH
        ppnDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');
            
            if (value) {
                this.value = formatNumber(value);
                ppnInput.value = value;
            } else {
                this.value = '';
                ppnInput.value = '0';
            }
            
            if (ppnInput.value != originalPpn) {
                ppnInput.dataset.manuallyEdited = 'true';
            }
            updateVendorTotal();
        });
        
        pph23Display.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');
            
            if (value) {
                this.value = formatNumber(value);
                pph23Input.value = value;
            } else {
                this.value = '';
                pph23Input.value = '0';
            }
            
            if (pph23Input.value != originalPph23) {
                pph23Input.dataset.manuallyEdited = 'true';
            }
            updateVendorTotal();
        });
    }
    
    // Auto-calculate PPN and PPH23 for Pelayaran costs
    const freightCostInput = document.getElementById('freight_cost_input');
    const ppnPelayaranInput = document.getElementById('ppn_pelayaran_input');
    const pph23PelayaranInput = document.getElementById('pph23_pelayaran_input');
    const pelayaranTotalDisplay = document.getElementById('pelayaran_total_display');
    
    function calculatePelayaranTax() {
        const freightCost = parseFloat(freightCostInput.value) || 0;
        
        // Auto-calculate PPN 11% (bisa di-override manual)
        if (!ppnPelayaranInput.dataset.manuallyEdited) {
            const ppn = Math.round(freightCost * 0.11);
            ppnPelayaranInput.value = ppn;
            document.getElementById('ppn_pelayaran_display').value = formatNumber(ppn);
        }
        
        // Auto-calculate PPH 23 2% (bisa di-override manual)
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
        const total = freightCost + ppn - pph23; // PPH 23 dipotong (minus)
        
        pelayaranTotalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
    
    if (freightCostInput) {
        freightCostInput.addEventListener('input', calculatePelayaranTax);
        
        // PPN and PPH inputs for pelayaran
        const ppnPelayaranDisplay = document.getElementById('ppn_pelayaran_display');
        const pph23PelayaranDisplay = document.getElementById('pph23_pelayaran_display');
        
        let originalPpnPelayaran = ppnPelayaranInput.value;
        let originalPph23Pelayaran = pph23PelayaranInput.value;
        
        ppnPelayaranDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');
            
            if (value) {
                this.value = formatNumber(value);
                ppnPelayaranInput.value = value;
            } else {
                this.value = '';
                ppnPelayaranInput.value = '0';
            }
            
            if (ppnPelayaranInput.value != originalPpnPelayaran) {
                ppnPelayaranInput.dataset.manuallyEdited = 'true';
            }
            updatePelayaranTotal();
        });
        
        pph23PelayaranDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');
            
            if (value) {
                this.value = formatNumber(value);
                pph23PelayaranInput.value = value;
            } else {
                this.value = '';
                pph23PelayaranInput.value = '0';
            }
            
            if (pph23PelayaranInput.value != originalPph23Pelayaran) {
                pph23PelayaranInput.dataset.manuallyEdited = 'true';
            }
            updatePelayaranTotal();
        });
    }
});
</script>
@endsection
