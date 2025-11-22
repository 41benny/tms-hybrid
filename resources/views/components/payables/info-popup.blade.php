@props(['id','title'])
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closePayablesPopup('{{ $id }}')"></div>
    <div class="relative w-full h-full md:h-auto md:max-w-2xl md:mx-auto md:mt-14 bg-white dark:bg-slate-900 rounded-none md:rounded-2xl shadow-2xl border-t md:border border-slate-200 dark:border-slate-700 flex flex-col md:max-h-[85vh]">
        <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-sm md:text-base font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</h2>
            <button type="button" onclick="closePayablesPopup('{{ $id }}')" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div id="{{ $id }}-content" class="p-4 md:p-6 space-y-3 overflow-y-auto flex-1">
            <div class="flex items-center justify-center py-12 text-slate-500 dark:text-slate-400">
                <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
        </div>
    </div>
</div>
