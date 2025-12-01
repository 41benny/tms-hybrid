<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(array_keys(User::availableRoles()))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($this->availablePermissions())],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedData(): array
    {
        $data = $this->validated();

        $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        if (! $this->has('permissions') || empty($data['permissions'] ?? [])) {
            // Null artinya: pakai default permissions berdasarkan role
            $data['permissions'] = null;
        } else {
            $data['permissions'] = array_values(array_unique($data['permissions'] ?? []));
        }

        return $data;
    }

    /**
        * @return list<string>
        */
    private function availablePermissions(): array
    {
        return collect(config('permissions.available_permissions', []))
            ->flatMap(fn (array $group) => $group['items'] ?? [])
            ->keys()
            ->values()
            ->all();
    }
}
