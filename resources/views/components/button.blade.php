@props(['variant' => 'primary', 'size' => 'md', 'icon' => null, 'iconPosition' => 'left', 'href' => null])

@php
    $baseClass = 'tms-btn inline-flex items-center justify-center gap-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed font-medium';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    
    $variantClasses = [
        'primary' => 'btn-primary bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white border border-white/20 shadow-sm hover:shadow-md focus:ring-[var(--color-primary)]',
        'secondary' => 'bg-slate-600 dark:bg-[#3d3d3d] hover:bg-slate-700 dark:hover:bg-[#4d4d4d] text-white shadow-sm hover:shadow-md focus:ring-slate-500',
        'success' => 'bg-emerald-600 dark:bg-emerald-800 hover:bg-emerald-700 dark:hover:bg-emerald-700 text-white shadow-sm hover:shadow-md focus:ring-emerald-500',
        'danger' => 'bg-rose-600 dark:bg-rose-800 hover:bg-rose-700 dark:hover:bg-rose-700 text-white shadow-sm hover:shadow-md focus:ring-rose-500',
        'warning' => 'bg-amber-600 dark:bg-amber-800 hover:bg-amber-700 dark:hover:bg-amber-700 text-white shadow-sm hover:shadow-md focus:ring-amber-500',
        'outline' => 'border border-slate-300 dark:border-white/20 hover:bg-slate-50 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300 focus:ring-slate-500',
        'ghost' => 'hover:bg-slate-100 dark:hover:bg-[#2d2d2d] text-slate-700 dark:text-slate-300',
    ];
    
    $classes = $baseClass . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <span>{{ $icon }}</span>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <span>{{ $icon }}</span>
        @endif
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        @if($icon && $iconPosition === 'left')
            <span>{{ $icon }}</span>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <span>{{ $icon }}</span>
        @endif
    </button>
@endif
