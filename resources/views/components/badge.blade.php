@props(['variant' => 'default'])
@php
    $base = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium border';
    $map = [
        'default' => 'bg-transparent text-slate-600 border-slate-400 dark:text-slate-300 dark:border-slate-500',
        'success' => 'bg-transparent text-emerald-600 border-emerald-500 dark:text-emerald-400 dark:border-emerald-500',
        'warning' => 'bg-transparent text-amber-600 border-amber-500 dark:text-amber-400 dark:border-amber-500',
        'danger' => 'bg-transparent text-rose-600 border-rose-500 dark:text-rose-400 dark:border-rose-500',
    ];
    $cls = $base.' '.($map[$variant] ?? $map['default']);
@endphp
<span {{ $attributes->merge(['class' => $cls]) }}>
    {{ $slot }}
@isset($icon)
    <span class="text-[10px]">{{ $icon }}</span>
@endisset
</span>

