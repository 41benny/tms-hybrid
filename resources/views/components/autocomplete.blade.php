@props(['name', 'label' => null, 'options' => [], 'value' => '', 'valueField' => 'id', 'textField' => 'name', 'placeholder' => '', 'required' => false, 'error' => null])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        <input 
            type="text"
            id="{{ $name }}_search"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="w-full rounded-lg bg-white dark:bg-[#252525] border {{ $error ? 'border-rose-500' : 'border-slate-300 dark:border-[#3d3d3d]' }} px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
        >
        <input type="hidden" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}">
        
        <div id="{{ $name }}_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-60 overflow-auto rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] shadow-lg">
            <div id="{{ $name }}_options" class="py-1">
                @foreach($options as $option)
                    <div class="autocomplete-option px-4 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 cursor-pointer text-slate-900 dark:text-slate-100" 
                         data-value="{{ is_array($option) || is_object($option) ? $option[$valueField] ?? $option->$valueField : $option }}"
                         data-text="{{ is_array($option) || is_object($option) ? $option[$textField] ?? $option->$textField : $option }}">
                        {{ is_array($option) || is_object($option) ? $option[$textField] ?? $option->$textField : $option }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    @if($error)
        <p class="text-sm text-rose-600 dark:text-rose-400 mt-1.5">{{ $error }}</p>
    @endif
</div>

<script>
(function() {
    const searchInput = document.getElementById('{{ $name }}_search');
    const hiddenInput = document.getElementById('{{ $name }}');
    const dropdown = document.getElementById('{{ $name }}_dropdown');
    const optionsContainer = document.getElementById('{{ $name }}_options');
    
    let allOptions = Array.from(optionsContainer.querySelectorAll('.autocomplete-option'));
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        if (searchTerm.length === 0) {
            dropdown.classList.add('hidden');
            hiddenInput.value = '';
            return;
        }
        
        let hasResults = false;
        allOptions.forEach(option => {
            const text = option.getAttribute('data-text').toLowerCase();
            if (text.includes(searchTerm)) {
                option.classList.remove('hidden');
                hasResults = true;
            } else {
                option.classList.add('hidden');
            }
        });
        
        if (hasResults) {
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    });
    
    searchInput.addEventListener('focus', function() {
        if (this.value.length > 0) {
            dropdown.classList.remove('hidden');
        }
    });
    
    optionsContainer.addEventListener('click', function(e) {
        const option = e.target.closest('.autocomplete-option');
        if (option) {
            searchInput.value = option.getAttribute('data-text');
            hiddenInput.value = option.getAttribute('data-value');
            dropdown.classList.add('hidden');
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
})();
</script>

