<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('menus')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => User::availableRoles(),
            'menus' => $this->groupedMenus(),
            'permissions' => config('permissions.available_permissions', []),
            'selectedPermissions' => collect(old('permissions', []))->all(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validatedData();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'],
            'deactivated_at' => $data['is_active'] ? null : now(),
        ]);

        $user->permissions = $data['permissions'];
        $user->save();

        $this->syncMenus($user, $data['menu_ids']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('User berhasil dibuat.'));
    }

    public function edit(User $user): View
    {
        $user->load('menus');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => User::availableRoles(),
            'menus' => $this->groupedMenus(),
            'selectedMenus' => $user->isSuperAdmin()
                ? Menu::query()->pluck('id')->all()
                : $user->menus->pluck('id')->all(),
            'permissions' => config('permissions.available_permissions', []),
            'selectedPermissions' => collect(old(
                'permissions',
                $user->permissions ?? (config('permissions.role_permissions')[$user->role] ?? [])
            ))->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // Protect the primary Super Admin (usually ID 1)
        $isPrimarySuperAdmin = $user->id === 1 && $user->isSuperAdmin();
        
        $data = $request->validatedData();

        $updates = [
            'name' => $data['name'],
            'email' => $isPrimarySuperAdmin ? $user->email : $data['email'], // Cannot change primary admin email
            'role' => $user->isSuperAdmin() ? User::ROLE_SUPER_ADMIN : $data['role'], // Cannot demote any super admin
            'is_active' => $isPrimarySuperAdmin ? true : $data['is_active'], // Cannot deactivate primary admin
        ];

        // Prevent deactivation of primary Super Admin
        if ($isPrimarySuperAdmin && $data['is_active'] === false) {
            return back()->withErrors([
                'is_active' => __('Akun Super Admin utama tidak dapat dinonaktifkan untuk keamanan sistem.'),
            ])->withInput();
        }

        if ($data['is_active'] === false && $user->deactivated_at === null) {
            $updates['deactivated_at'] = now();
        } elseif ($data['is_active'] === true) {
            $updates['deactivated_at'] = null;
        }

        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);
        
        // Primary Super Admin always has all permissions
        if (!$isPrimarySuperAdmin) {
            $user->permissions = $data['permissions'];
            $user->save();
        }

        $this->syncMenus($user, $data['menu_ids']);

        if (! $user->is_active && $user->is(Auth::user())) {
            Auth::logout();
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('User berhasil diperbarui.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin() || $user->is(Auth::user())) {
            return back()->withErrors([
                'user' => __('Tidak dapat menghapus akun ini.'),
            ]);
        }

        $user->menus()->detach();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('User berhasil dihapus.'));
    }

    /**
     * @return Collection<string, Collection<int, Menu>>
     */
    private function groupedMenus(): Collection
    {
        return Menu::query()
            ->orderBy('section')
            ->orderBy('sort')
            ->get()
            ->groupBy(fn (Menu $menu): string => $menu->section ?? 'general');
    }

    /**
     * @param  list<int>  $menuIds
     */
    private function syncMenus(User $user, array $menuIds): void
    {
        if ($user->isSuperAdmin()) {
            $user->menus()->sync(Menu::query()->pluck('id')->all());

            return;
        }

        $user->menus()->sync($menuIds);
    }
}
