@props(['variant' => 'info', 'title' => null])

@php
    $base = 'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm';
    $map = [
        'info' => 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/30 dark:text-sky-100 dark:border-sky-800',
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-100 dark:border-emerald-800',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-100 dark:border-amber-800',
        'danger' => 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-900/30 dark:text-rose-100 dark:border-rose-800',
    ];
    $cls = $base.' '.($map[$variant] ?? $map['info']);
@endphp

<div {{ $attributes->merge(['class' => $cls]) }}>
    <div class="mt-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v4m0 4h.01M4.93 19h14.14a1 1 0 00.86-1.5L12.86 4.5a1 1 0 00-1.72 0L4.07 17.5A1 1 0 004.93 19z" />
        </svg>
    </div>
    <div class="space-y-1">
        @if($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div>
            {{ $slot }}
        </div>
    </div>
</div>

