@php
    $selectedPermissions = collect(old('permissions', $selectedPermissions ?? []))->all();
    
    // Group menus by section for display
    $menusBySection = [];
    foreach ($menus as $section => $menuGroup) {
        $menusBySection[$section] = $menuGroup;
    }
@endphp

<div class="space-y-8" x-data="userPermissionsManager()">
    {{-- User Information Section --}}
    <div class="rounded-2xl border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Informasi User
        </h3>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium text-slate-600 dark:text-slate-300">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name ?? '') }}"
                        required
                        class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-slate-50 dark:bg-[#1c1c1c] px-4 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-[#262626] transition-colors"
                        placeholder="Masukkan nama lengkap"
                    >
                    @error('name')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-slate-600 dark:text-slate-300">Alamat Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email ?? '') }}"
                        required
                        {{ isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin ? 'readonly' : '' }}
                        class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-slate-50 dark:bg-[#1c1c1c] px-4 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-[#262626] transition-colors {{ isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin ? 'opacity-60 cursor-not-allowed' : '' }}"
                        placeholder="nama@example.com"
                    >
                    @if(isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin)
                        <p class="text-xs text-amber-600 dark:text-amber-400">Email Super Admin utama tidak dapat diubah</p>
                    @endif
                    @error('email')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-slate-600 dark:text-slate-300">Role / Peran</label>
                    <div class="relative">
                        <select
                            id="role"
                            name="role"
                            class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-slate-50 dark:bg-[#1c1c1c] px-4 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-[#262626] appearance-none transition-colors"
                            required
                        >
                            <option value="" disabled {{ old('role', $user->role ?? '') === '' ? 'selected' : '' }}>Pilih role user...</option>
                            @foreach ($roles as $roleValue => $roleLabel)
                                <option value="{{ $roleValue }}" @selected(old('role', $user->role ?? '') === $roleValue)>{{ $roleLabel }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    @error('role')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-slate-600 dark:text-slate-300">
                        Password {{ isset($user) ? '(Opsional)' : '' }}
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        {{ isset($user) ? '' : 'required' }}
                        class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-slate-50 dark:bg-[#1c1c1c] px-4 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-[#262626] transition-colors"
                        placeholder="{{ isset($user) ? 'Biarkan kosong jika tidak ingin mengubah' : 'Buat password baru' }}"
                    >
                    @error('password')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-slate-600 dark:text-slate-300">Konfirmasi Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        {{ isset($user) ? '' : 'required' }}
                        class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-slate-50 dark:bg-[#1c1c1c] px-4 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-[#262626] transition-colors"
                        placeholder="Ulangi password"
                    >
                </div>

                <div class="space-y-2">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Status Akun</span>
                    <div class="flex items-center gap-4 mt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer {{ isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin ? 'opacity-60 cursor-not-allowed' : '' }}">
                            <input 
                                type="radio" 
                                name="is_active" 
                                value="1" 
                                @checked((int) old('is_active', $user->is_active ?? 1) === 1) 
                                {{ isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin ? 'disabled' : '' }}
                                class="text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer {{ isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin ? 'opacity-60 cursor-not-allowed' : '' }}">
                            <input 
                                type="radio" 
                                name="is_active" 
                                value="0" 
                                @checked((int) old('is_active', $user->is_active ?? 1) === 0) 
                                {{ isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin ? 'disabled' : '' }}
                                class="text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-slate-700 dark:text-slate-300">Nonaktif</span>
                        </label>
                    </div>
                    @if(isset($isPrimarySuperAdmin) && $isPrimarySuperAdmin)
                        <p class="text-xs text-amber-600 dark:text-amber-400">Super Admin utama tidak dapat dinonaktifkan</p>
                    @endif
                    @error('is_active')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Permissions Section --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Hak Akses & Izin
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Atur izin akses untuk setiap modul dan fitur</p>
            </div>
            <div class="text-xs px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30">
                <span class="font-medium">💡 Tip:</span> Centang section untuk aktifkan semua menu di dalamnya
            </div>
        </div>

        @foreach ($menusBySection as $section => $menuGroup)
            @php
                $sectionId = Str::slug($section);
            @endphp
            
            <div class="rounded-2xl border-2 border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                {{-- Section Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-[#222] dark:to-[#1a1a1a] border-b border-slate-200 dark:border-[#2d2d2d]">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <input
                                type="checkbox"
                                @change="toggleSection('{{ $sectionId }}', $el.checked)"
                                class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                            >
                            <div>
                                <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ Str::headline($section) }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ count($menuGroup) }} menu tersedia</p>
                            </div>
                        </label>
                        <button 
                            type="button"
                            @click="toggleSectionCollapse('{{ $sectionId }}')"
                            class="p-2 rounded-lg hover:bg-white/50 dark:hover:bg-[#2d2d2d] transition-colors"
                        >
                            <svg 
                                class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                :class="collapsedSections.includes('{{ $sectionId }}') ? '' : 'rotate-180'"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Section Content --}}
                <div 
                    x-show="!collapsedSections.includes('{{ $sectionId }}')"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="p-6 space-y-6"
                >
                    @foreach ($menuGroup as $menu)
                        @php
                            $permKey = str_replace('-', '_', $menu->slug);
                            $groupPermissions = $permissions[$permKey] ?? null;
                            $hasPermissions = !empty($groupPermissions['items']);
                        @endphp

                        <div class="space-y-3" data-section="{{ $sectionId }}" data-menu="{{ $permKey }}">
                            {{-- Menu Row --}}
                            <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-[#1c1c1c] border border-slate-200 dark:border-[#2d2d2d]">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-1.5 h-6 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
                                        <h5 class="font-semibold text-slate-800 dark:text-slate-100">{{ $menu->label }}</h5>
                                        @if(!$hasPermissions)
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 dark:bg-[#2d2d2d] text-slate-500 dark:text-slate-400">View Only</span>
                                        @endif
                                    </div>

                                    {{-- Permission Actions --}}
                                    @if($hasPermissions)
                                        <div class="flex flex-wrap gap-2 ml-5">
                                            @foreach ($groupPermissions['items'] as $pKey => $pLabel)
                                                <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border-2 border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#262626] hover:border-indigo-400 dark:hover:border-indigo-600 hover:shadow-sm cursor-pointer select-none group transition-all">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $pKey }}"
                                                        data-section="{{ $sectionId }}"
                                                        data-menu="{{ $permKey }}"
                                                        @checked(in_array($pKey, $selectedPermissions, true))
                                                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                                                    >
                                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white">{{ $pLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-slate-400 italic ml-5">Menu ini tidak memiliki izin aksi tambahan</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @error('permissions')
            <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            </div>
        @enderror
    </div>
</div>

<script>
    function userPermissionsManager() {
        return {
            collapsedSections: [],

            init() {
                // Initialize - sections are expanded by default
            },

            toggleSection(sectionId, isChecked) {
                // Find all permission checkboxes in this section
                const sectionCheckboxes = document.querySelectorAll(`input[data-section="${sectionId}"][name="permissions[]"]`);
                
                // Check/uncheck all permissions in this section
                sectionCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            },

            toggleSectionCollapse(sectionId) {
                const index = this.collapsedSections.indexOf(sectionId);
                if (index > -1) {
                    this.collapsedSections.splice(index, 1);
                } else {
                    this.collapsedSections.push(sectionId);
                }
            }
        }
    }
</script>
