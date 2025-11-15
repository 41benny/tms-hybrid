@extends('layouts.app', ['title' => 'Edit Jurnal'])

@section('content')
<div class="space-y-6" x-data="journalForm()">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Edit Jurnal</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $journal->journal_no }}</p>
                </div>
                <x-button :href="route('journals.show', $journal)" variant="ghost" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </x-button>
            </div>
        </x-slot:header>
    </x-card>

    <form method="post" action="{{ route('journals.update', $journal) }}" class="space-y-6" @submit.prevent="submitForm">
        @csrf
        @method('PUT')
        
        <x-card title="Header Jurnal">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm mb-1">Tanggal Jurnal <span class="text-red-500">*</span></label>
                    <input type="date" name="journal_date" x-model="formData.journal_date" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @error('journal_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Mata Uang</label>
                    <input type="text" name="currency" x-model="formData.currency" maxlength="3" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    @error('currency')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div></div>
            </div>
            <div class="mt-4">
                <label class="block text-sm mb-1">Memo/Keterangan</label>
                <textarea name="memo" x-model="formData.memo" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" rows="2"></textarea>
                @error('memo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <x-card title="Detail Jurnal">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Total Debit: <span class="font-semibold" x-text="formatNumber(totalDebit)"></span></p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Total Kredit: <span class="font-semibold" x-text="formatNumber(totalCredit)"></span></p>
                    <p class="text-sm" :class="isBalanced ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                        Selisih: <span class="font-semibold" x-text="formatNumber(Math.abs(totalDebit - totalCredit))"></span>
                    </p>
                </div>
                <button type="button" @click="addLine" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">+ Tambah Baris</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-slate-200 dark:border-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-3 py-2 text-left border-b">Akun</th>
                            <th class="px-3 py-2 text-right border-b">Debit</th>
                            <th class="px-3 py-2 text-right border-b">Kredit</th>
                            <th class="px-3 py-2 text-left border-b">Keterangan</th>
                            <th class="px-3 py-2 border-b"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in formData.lines" :key="index">
                            <tr>
                                <td class="px-3 py-2 border-b">
                                    <select 
                                        :name="`lines[${index}][account_id]`" 
                                        x-model="line.account_id"
                                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-1" 
                                        required
                                    >
                                        <option value="">Pilih Akun</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2 border-b">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        :name="`lines[${index}][debit]`" 
                                        x-model.number="line.debit"
                                        @input="updateTotals"
                                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-1 text-right"
                                        placeholder="0"
                                    >
                                </td>
                                <td class="px-3 py-2 border-b">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        :name="`lines[${index}][credit]`" 
                                        x-model.number="line.credit"
                                        @input="updateTotals"
                                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-1 text-right"
                                        placeholder="0"
                                    >
                                </td>
                                <td class="px-3 py-2 border-b">
                                    <input 
                                        type="text" 
                                        :name="`lines[${index}][description]`" 
                                        x-model="line.description"
                                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-1"
                                        placeholder="Keterangan"
                                    >
                                </td>
                                <td class="px-3 py-2 border-b">
                                    <button type="button" @click="removeLine(index)" class="text-red-600 hover:text-red-800" :disabled="formData.lines.length <= 2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            @error('lines')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </x-card>

        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('journals.show', $journal)" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary" :disabled="!isBalanced">Simpan Perubahan</x-button>
            </div>
        </x-card>
    </form>
</div>

<script>
function journalForm() {
    return {
        formData: {
            journal_date: '{{ $journal->journal_date->format('Y-m-d') }}',
            currency: '{{ $journal->currency }}',
            memo: @json($journal->memo),
            lines: @json($journal->lines->map(fn($line) => [
                'account_id' => $line->account_id,
                'debit' => (float)$line->debit,
                'credit' => (float)$line->credit,
                'description' => $line->description ?? ''
            ]))
        },
        totalDebit: 0,
        totalCredit: 0,
        
        init() {
            this.updateTotals();
        },
        
        addLine() {
            this.formData.lines.push({ account_id: '', debit: 0, credit: 0, description: '' });
        },
        
        removeLine(index) {
            if (this.formData.lines.length > 2) {
                this.formData.lines.splice(index, 1);
                this.updateTotals();
            }
        },
        
        updateTotals() {
            this.totalDebit = this.formData.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
            this.totalCredit = this.formData.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
        },
        
        get isBalanced() {
            return Math.abs(this.totalDebit - this.totalCredit) < 0.01 && this.totalDebit > 0;
        },
        
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
        },
        
        submitForm() {
            if (!this.isBalanced) {
                alert('Total debit dan kredit harus seimbang!');
                return false;
            }
            this.$el.submit();
        }
    }
}
</script>
@endsection

