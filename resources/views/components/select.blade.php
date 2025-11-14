@props(['label' => null, 'name' => null, 'error' => null, 'required' => false, 'helpText' => null, 'options' => []])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif
    
    <select 
        {{ $attributes->except('class')->merge([
            'class' => 'w-full rounded-lg bg-white dark:bg-[#252525] border ' . 
                      ($error ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 dark:border-[#3d3d3d] focus:border-indigo-500 focus:ring-indigo-500') . 
                      ' px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-offset-0 transition-colors shadow-sm [&>option]:bg-white [&>option]:dark:bg-[#252525] [&>option]:text-slate-900 [&>option]:dark:text-slate-100',
            'id' => $name,
            'name' => $name,
        ]) }}
    >
        {{ $slot }}
    </select>
    
    @if($helpText)
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">{{ $helpText }}</p>
    @endif
    
    @if($error)
        <p class="text-sm text-rose-600 dark:text-rose-400 mt-1.5">{{ $error }}</p>
    @endif
</div>

