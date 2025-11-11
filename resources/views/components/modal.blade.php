@props(['id', 'title' => '', 'maxWidth' => 'md'])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        default => 'max-w-lg',
    };
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity modal-backdrop" onclick="document.getElementById('{{ $id }}').classList.add('hidden')"></div>
        
        {{-- Modal Panel --}}
        <div class="relative {{ $maxWidthClass }} w-full transform overflow-hidden rounded-2xl bg-white dark:bg-[#252525] shadow-2xl transition-all border border-slate-200 dark:border-[#2d2d2d]">
            {{-- Header --}}
            @if($title || isset($header))
                <div class="border-b border-slate-200 dark:border-[#2d2d2d] px-6 py-4 bg-gradient-to-r from-slate-50 to-transparent dark:from-slate-950 dark:to-transparent">
                    @isset($header)
                        {{ $header }}
                    @else
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h3>
                    @endisset
                </div>
            @endif
            
            {{-- Body --}}
            <div class="px-6 py-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

