@props(['variant' => 'default'])
@php
    $base = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium border';
    $map = [
        'default' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700',
        'success' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800',
        'danger' => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-800',
    ];
    $cls = $base.' '.($map[$variant] ?? $map['default']);
@endphp
<span {{ $attributes->merge(['class' => $cls]) }}>
    {{ $slot }}
@isset($icon)
    <span class="text-[10px]">{{ $icon }}</span>
@endisset
</span>

