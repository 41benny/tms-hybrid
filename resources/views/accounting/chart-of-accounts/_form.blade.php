@props([
    'account',
    'types' => [],
    'statuses' => [],
    'parentOptions' => [],
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
])

@php
    $typeValue = old('type', $account->type ?? 'asset');
    $statusValue = old('status', $account->status ?? 'active');
    $parentValue = old('parent_id', $account->parent_id);
    $isPostable = (int) old('is_postable', $account->is_postable ?? true);
    $isCash = (int) old('is_cash', $account->is_cash ?? false);
    $isBank = (int) old('is_bank', $account->is_bank ?? false);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if(! in_array($method, ['POST', 'GET']))
        @method($method)
    @endif

    <x-card title="Informasi Akun">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-600 dark:text-slate-300">Kode Akun <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    name="code" 
                    value="{{ old('code', $account->code) }}" 
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2" 
                    placeholder="Misal: 1100" 
                    required
                >
                @error('code')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 dark:text-slate-300">Nama Akun <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ old('name', $account->name) }}" 
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2" 
                    placeholder="Kas Besar" 
                    required
                >
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 dark:text-slate-300">Tipe Akun</label>
                <select 
                    name="type" 
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2"
                >
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected($typeValue === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 dark:text-slate-300">Parent</label>
                <select 
                    name="parent_id" 
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2"
                >
                    <option value="">-- Root --</option>
                    @foreach($parentOptions as $option)
                        <option value="{{ $option->id }}" @selected((int) $parentValue === (int) $option->id)">
                            {{ $option->code }} - {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="p-4 rounded-lg border border-slate-200 dark:border-[#3d3d3d] bg-white/50 dark:bg-[#1e1e1e]/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Properti Akun</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tentukan apakah akun dapat diposting langsung atau hanya sebagai header.</p>
                <div class="mt-4 space-y-3">
                    <input type="hidden" name="is_postable" value="0">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_postable" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($isPostable)>
                        <span>Postable (bisa dipilih saat transaksi)</span>
                    </label>
                </div>
                @error('is_postable')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="p-4 rounded-lg border border-slate-200 dark:border-[#3d3d3d] bg-white/50 dark:bg-[#1e1e1e]/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Flag Kas / Bank</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Aktifkan jika akun ini dipakai untuk modul Cash/Bank. Hanya berlaku untuk tipe aset & postable.</p>
                <div class="mt-4 space-y-3">
                    <input type="hidden" name="is_cash" value="0">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_cash" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($isCash)>
                        <span>Tandai sebagai Kas</span>
                    </label>
                    <input type="hidden" name="is_bank" value="0">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_bank" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($isBank)>
                        <span>Tandai sebagai Bank</span>
                    </label>
                </div>
                @if ($typeValue !== 'asset')
                    <p class="text-xs text-amber-600 mt-2">Flag hanya akan aktif untuk tipe aset.</p>
                @endif
            </div>
            <div class="p-4 rounded-lg border border-slate-200 dark:border-[#3d3d3d] bg-white/50 dark:bg-[#1e1e1e]/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Status</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Nonaktifkan akun jika tidak lagi digunakan.</p>
                <select 
                    name="status" 
                    class="mt-4 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2"
                >
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($statusValue === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </x-card>

    <div class="flex justify-end gap-3">
        <x-button :href="route('chart-of-accounts.index')" variant="outline">Batal</x-button>
        <x-button type="submit" variant="primary">{{ $submitLabel }}</x-button>
    </div>
</form>

