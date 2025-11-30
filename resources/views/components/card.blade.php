@props(['title' => null, 'subtitle' => null, 'headerClass' => '', 'bodyClass' => '', 'noPadding' => false, 'collapsible' => false, 'collapsed' => false])

<div 
    {{ $attributes->merge(['class' => 'rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all']) }}
    x-data="{ open: {{ $collapsed ? 'false' : 'true' }} }"
>
    @if($title || $subtitle || isset($header))
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 {{ $headerClass }}">
            @isset($header)
                {{ $header }}
            @else
                <div class="flex items-center justify-between cursor-pointer" @if($collapsible) @click="open = !open" @endif>
                    <div class="flex-1">
                        @if($title)
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                {{ $title }}
                            </h3>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                    
                    @if($collapsible)
                        <button type="button" class="p-1 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-transform duration-200" :class="{ 'rotate-180': !open }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                    @endif
                </div>
            @endisset
        </div>
    @endif
    
    <div 
        class="{{ $noPadding ? '' : 'p-6' }} {{ $bodyClass }}"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
    >
        {{ $slot }}
    </div>
</div>