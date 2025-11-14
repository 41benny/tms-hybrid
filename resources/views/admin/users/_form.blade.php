@php
    $selectedMenus = collect(old('menu_ids', $selectedMenus ?? []))->map(fn ($id) => (int) $id)->all();
@endphp
<div class="grid gap-5 md:grid-cols-2">
    <div class="space-y-4">
        <div class="space-y-2">
            <label for="name" class="text-sm font-medium text-slate-600 dark:text-slate-300">Nama</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name ?? '') }}"
                required
                class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            @error('name')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-slate-600 dark:text-slate-300">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email ?? '') }}"
                required
                class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            @error('email')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div class="space-y-2">
            <label for="role" class="text-sm font-medium text-slate-600 dark:text-slate-300">Role</label>
            <select
                id="role"
                name="role"
                class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                required
            >
                <option value="" disabled {{ old('role', $user->role ?? '') === '' ? 'selected' : '' }}>Pilih role</option>
                @foreach ($roles as $roleValue => $roleLabel)
                    <option value="{{ $roleValue }}" @selected(old('role', $user->role ?? '') === $roleValue)>{{ $roleLabel }}</option>
                @endforeach
            </select>
            @error('role')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div class="space-y-2">
            <label for="password" class="text-sm font-medium text-slate-600 dark:text-slate-300">
                Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }}
            </label>
            <input
                type="password"
                id="password"
                name="password"
                {{ isset($user) ? '' : 'required' }}
                class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                class="w-full rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
        </div>
        <div class="space-y-2">
            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Status</span>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="radio" name="is_active" value="1" @checked((int) old('is_active', $user->is_active ?? 1) === 1)>
                    Aktif
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="radio" name="is_active" value="0" @checked((int) old('is_active', $user->is_active ?? 1) === 0)>
                    Nonaktif
                </label>
            </div>
            @error('is_active')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="space-y-4">
        <div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Akses Menu</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                Checklist menu yang dapat diakses user. Super Admin otomatis memiliki semua menu.
            </p>
            <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                @foreach ($menus as $section => $menuGroup)
                    <div class="rounded-lg border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e]">
                        <div class="px-4 py-2 bg-slate-100 dark:bg-[#2d2d2d] text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            {{ Str::headline($section) }}
                        </div>
                        <div class="px-4 py-3 space-y-2">
                            @foreach ($menuGroup as $menu)
                                <label class="flex items-center justify-between gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <span>{{ $menu->label }}</span>
                                    <input
                                        type="checkbox"
                                        name="menu_ids[]"
                                        value="{{ $menu->id }}"
                                        @checked(in_array($menu->id, $selectedMenus, true))
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('menu_ids')
                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

