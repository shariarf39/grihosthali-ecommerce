<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderByDesc('is_system')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.form', ['role' => new Role(['permissions' => ['dashboard.view']])]);
    }

    public function store(Request $request)
    {
        $role = Role::create($this->validated($request));

        return redirect()->route('admin.roles.index')->with('success', "Role “{$role->name}” created.");
    }

    public function edit(Role $role)
    {
        if ($role->isSuperAdmin()) {
            return redirect()->route('admin.roles.index')->with('error', 'The Super Admin role always has full access and cannot be edited.');
        }

        return view('admin.roles.form', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->isSuperAdmin(), 403);
        $role->update($this->validated($request, $role));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system || $role->users()->exists()) {
            return back()->with('error', 'This role is in use or built-in and cannot be deleted.');
        }
        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($role?->id)],
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in(Permissions::all()),
        ]);
        $data['permissions'] = array_values($data['permissions'] ?? []);
        if (! $role) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));
        }

        return $data;
    }
}
