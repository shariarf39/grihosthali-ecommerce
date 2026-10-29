<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::where('is_admin', false)
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")))
            ->withCount('orders')->withSum('orders', 'total')
            ->latest()->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user)
    {
        $orders = $user->orders()->latest()->paginate(15);

        return view('admin.customers.show', compact('user', 'orders'));
    }

    public function destroy(User $user)
    {
        abort_if($user->is_admin, 403);
        $user->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }
}
