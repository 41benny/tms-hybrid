@props(['variant' => 'primary', 'size' => 'md', 'icon' => null, 'iconPosition' => 'left', 'href' => null])

@php
    $baseClass = 'inline-flex items-center justify-center gap-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    
    $variantClasses = [
        'primary' => 'bg-indigo-600 dark:bg-[#3d3d3d] hover:bg-indigo-700 dark:hover:bg-[#4d4d4d] text-white shadow-md dark:shadow-black/30 hover:shadow-lg focus:ring-indigo-500 dark:focus:ring-slate-500',
        'secondary' => 'bg-slate-600 dark:bg-[#3d3d3d] hover:bg-slate-700 dark:hover:bg-[#4d4d4d] text-white shadow-md dark:shadow-black/30 hover:shadow-lg focus:ring-slate-500',
        'success' => 'bg-emerald-600 dark:bg-emerald-800 hover:bg-emerald-700 dark:hover:bg-emerald-700 text-white shadow-md dark:shadow-black/30 hover:shadow-lg focus:ring-emerald-500',
        'danger' => 'bg-rose-600 dark:bg-rose-800 hover:bg-rose-700 dark:hover:bg-rose-700 text-white shadow-md dark:shadow-black/30 hover:shadow-lg focus:ring-rose-500',
        'warning' => 'bg-amber-600 dark:bg-amber-800 hover:bg-amber-700 dark:hover:bg-amber-700 text-white shadow-md dark:shadow-black/30 hover:shadow-lg focus:ring-amber-500',
        'outline' => 'border-2 border-slate-300 dark:border-[#3d3d3d] hover:bg-slate-50 dark:hover:bg-[#2d2d2d] text-slate-700 dark:text-slate-300 focus:ring-slate-500',
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

