<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\RoleController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserApiController extends BaseApiController
{
    /**
     * Get paginated users listing with search and role filter.
     */
    public function getUsers(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');
        $roleFilter = $request->query('role');
        $perPage = (int) ($request->query('per_page', 50));

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, function ($query, $roleFilter) {
                $query->whereHas('roles', function ($q) use ($roleFilter) {
                    $q->where('name', $roleFilter);
                });
            })
            ->latest()
            ->paginate($perPage);

        return $this->sendResponse([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 'Daftar staf & pengguna berhasil dimuat.');
    }

    /**
     * Show single user detail.
     */
    public function showUser(User $user): JsonResponse
    {
        return $this->sendResponse(
            $user->load('roles.permissions'),
            'Detail pengguna berhasil dimuat.'
        );
    }

    /**
     * Store a newly created user.
     */
    public function storeUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap staf wajib diisi.',
            'email.required' => 'Alamat email staf wajib diisi.',
            'email.unique' => 'Alamat email sudah digunakan oleh pengguna lain.',
            'password.required' => 'Kata sandi wajib diisi (minimal 8 karakter).',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran (role) staf wajib dipilih.',
            'role.exists' => 'Peran (role) yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        return $this->sendResponse(
            $user->load('roles'),
            "Pengguna '{$user->name}' berhasil didaftarkan.",
            201
        );
    }

    /**
     * Update user details.
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap staf wajib diisi.',
            'email.required' => 'Alamat email staf wajib diisi.',
            'email.unique' => 'Alamat email sudah digunakan oleh pengguna lain.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran (role) staf wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);
        $user->syncRoles([$validated['role']]);

        return $this->sendResponse(
            $user->load('roles'),
            "Data staf '{$user->name}' berhasil diperbarui."
        );
    }

    /**
     * Delete user with protection.
     */
    public function destroyUser(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()?->id) {
            return $this->sendError('Anda tidak dapat menghapus akun Anda yang sedang aktif masuk.', [], 403);
        }

        if ($user->hasRole('super_admin') && User::role('super_admin')->count() <= 1) {
            return $this->sendError('Tidak dapat menghapus satu-satunya Super Administrator sistem.', [], 403);
        }

        $userName = $user->name;
        $user->delete();

        return $this->sendResponse(null, "Pengguna '{$userName}' berhasil dihapus dari sistem.");
    }

    /**
     * Get list of all roles with user count and permissions.
     */
    public function getRoles(): JsonResponse
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderByRaw("CASE WHEN name = 'super_admin' THEN 1 WHEN name = 'owner' THEN 2 WHEN name = 'manager' THEN 3 WHEN name = 'cashier' THEN 4 ELSE 5 END")
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'users_count' => (int) $role->users_count,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ];
            });

        $modules = RoleController::getPermissionModules();
        $totalPermissions = Permission::count();

        return $this->sendResponse([
            'roles' => $roles,
            'permission_modules' => $modules,
            'total_permissions_count' => $totalPermissions,
        ], 'Daftar peran & hak akses berhasil dimuat.');
    }

    /**
     * Store new custom role.
     */
    public function storeRole(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\-\s]+$/', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'Nama peran (role) wajib diisi.',
            'name.unique' => 'Nama peran ini sudah ada, pilih nama lain.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $roleName = strtolower(trim(str_replace(' ', '_', $validated['name'])));

        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $this->sendResponse(
            $role->load('permissions')->loadCount('users'),
            "Peran baru '{$role->name}' berhasil dibuat.",
            201
        );
    }

    /**
     * Update existing role & permissions.
     */
    public function updateRole(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\-\s]+$/', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        if ($role->name === 'super_admin') {
            $role->syncPermissions(Permission::all());
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return $this->sendResponse(
                $role->load('permissions')->loadCount('users'),
                'Peran Super Administrator selalu memiliki hak akses penuh.'
            );
        }

        $role->update([
            'name' => strtolower(trim(str_replace(' ', '_', $validated['name']))),
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $this->sendResponse(
            $role->load('permissions')->loadCount('users'),
            "Hak akses peran '{$role->name}' berhasil diperbarui."
        );
    }

    /**
     * Delete custom role.
     */
    public function destroyRole(Role $role): JsonResponse
    {
        if ($role->name === 'super_admin') {
            return $this->sendError('Peran Super Administrator dilindungi dan tidak dapat dihapus.', [], 403);
        }

        if ($role->users()->count() > 0) {
            return $this->sendError("Peran '{$role->name}' masih digunakan oleh {$role->users()->count()} pengguna. Silakan pindahkan peran pengguna sebelum menghapus.", [], 422);
        }

        $roleName = $role->name;
        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $this->sendResponse(null, "Peran '{$roleName}' berhasil dihapus.");
    }
}
