<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::where('is_admin', true)->with('role')->orderBy('name')->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.form', ['member' => new User(['is_active' => true]), 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        $member = User::create($data + ['is_admin' => true]);

        return redirect()->route('admin.staff.index')->with('success', "{$member->name} can now log in to the admin panel.");
    }

    public function edit(User $staff)
    {
        abort_unless($staff->is_admin, 404);

        return view('admin.staff.form', ['member' => $staff, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->is_admin, 404);
        $data = $this->validated($request, $staff);

        if ($error = $this->guardLastSuperAdmin($staff, Role::find($data['role_id']), $data['is_active'])) {
            return back()->with('error', $error);
        }
        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $staff->update($data);

        return redirect()->route('admin.staff.index')->with('success', 'Admin updated.');
    }

    public function destroy(User $staff)
    {
        abort_unless($staff->is_admin, 404);
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($error = $this->guardLastSuperAdmin($staff, null, false)) {
            return back()->with('error', $error);
        }
        $staff->delete();

        return back()->with('success', 'Admin removed.');
    }

    public function activity(Request $request)
    {
        $logs = AdminLog::with('user:id,name')
            ->when($request->user, fn ($q, $u) => $q->where('user_id', $u))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('action', 'like', "%$s%")->orWhere('description', 'like', "%$s%")))
            ->latest()->paginate(40)->withQueryString();
        $staff = User::where('is_admin', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.staff.activity', compact('logs', 'staff'));
    }

    private function validated(Request $request, ?User $staff = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($staff?->id)],
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** There must always be at least one active Super Admin. */
    private function guardLastSuperAdmin(User $staff, ?Role $newRole, bool $stillActive): ?string
    {
        if (! $staff->isSuperAdmin()) {
            return null;
        }
        $remainsSuper = $newRole?->isSuperAdmin() && $stillActive;
        $others = User::where('is_admin', true)->where('is_active', true)->whereKeyNot($staff->id)
            ->whereHas('role', fn ($q) => $q->whereJsonContains('permissions', '*'))->exists();

        return (! $remainsSuper && ! $others) ? 'At least one active Super Admin is required.' : null;
    }
}
