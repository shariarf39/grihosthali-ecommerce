<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->withCount('items')->latest()->paginate(10);

        return view('account.index', compact('user', 'orders'));
    }

    public function order(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()->where('order_number', $orderNumber)->with('items')->firstOrFail();

        return view('account.order', compact('order'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^(\+?88)?01[3-9]\d{8}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'address' => 'nullable|string|max:500',
        ]);
        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $request->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed.');
    }
}
