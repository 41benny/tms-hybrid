@props(['title' => null, 'subtitle' => null, 'headerClass' => '', 'bodyClass' => '', 'noPadding' => false])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#252525] shadow-sm overflow-hidden']) }}>
    @if($title || $subtitle || isset($header))
        <div class="px-6 py-4 border-b border-slate-200 dark:border-[#2d2d2d] bg-gradient-to-r from-slate-50 to-transparent dark:from-[#2d2d2d]/30 dark:to-transparent {{ $headerClass }}">
            @isset($header)
                {{ $header }}
            @else
                <div class="flex items-center justify-between">
                    <div>
                        @if($title)
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h3>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="flex items-center gap-2">
                            {{ $actions }}
                        </div>
                    @endisset
                </div>
            @endisset
        </div>
    @endif
    
    <div class="{{ $noPadding ? '' : 'p-6' }} {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
