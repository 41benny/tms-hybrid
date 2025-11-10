<div {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm']) }}>
    @isset($title)
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-medium">{{ $title }}</div>
    @endisset
    <div class="p-4">
        {{ $slot }}
    </div>
</div>

