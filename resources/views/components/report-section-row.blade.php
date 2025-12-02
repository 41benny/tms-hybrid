@props(['title', 'amount', 'items', 'isExpense' => false])

<tbody x-data="{ expanded: false }" class="border-b border-slate-100 dark:border-slate-800">
    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer" @click="expanded = !expanded">
        <td class="px-6 py-3">
            <div class="flex items-center gap-3">
                <button class="text-slate-400 hover:text-indigo-500 transition-colors p-1 rounded-md hover:bg-slate-200 dark:hover:bg-slate-700">
                    <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <span class="font-bold text-slate-700 dark:text-slate-300 uppercase text-sm tracking-wide">{{ $title }}</span>
                <span class="text-xs text-slate-400 font-normal ml-2" x-show="!expanded">({{ count($items) }} akun)</span>
            </div>
        </td>
        <td class="px-6 py-3 text-right font-bold font-mono text-slate-800 dark:text-slate-200">
            {{ $isExpense && $amount > 0 ? '(' . number_format($amount, 2, ',', '.') . ')' : number_format($amount, 2, ',', '.') }}
        </td>
    </tr>
    
    <tr x-show="expanded" x-collapse style="display: none;">
        <td colspan="2" class="p-0">
            <div class="bg-slate-50/50 dark:bg-slate-800/30 border-y border-slate-100 dark:border-slate-800/50 py-2">
                <table class="w-full">
                    @foreach($items as $item)
                        <tr class="hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition-colors">
                            <td class="px-6 py-1.5 pl-16 text-sm text-slate-600 dark:text-slate-400 w-[calc(100%-12rem)]">
                                <span class="font-mono text-xs text-slate-400 mr-2">{{ $item->code }}</span>
                                {{ $item->name }}
                            </td>
                            <td class="px-6 py-1.5 text-right font-mono text-sm text-slate-700 dark:text-slate-300 w-48">
                                {{ number_format($item->net, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    @if(count($items) === 0)
                        <tr>
                            <td colspan="2" class="px-6 py-3 text-center text-sm text-slate-400 italic">
                                Tidak ada transaksi pada periode ini
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </td>
    </tr>
</tbody>
