<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private function getAllowedRoles(bool $includeCustom = true): array
    {
        $currentUser = auth()->user();
        $system = $currentUser->isSuperAdmin()
            ? ['super_admin', 'admin', 'manager', 'cashier']
            : ['admin', 'manager', 'cashier'];

        if ($includeCustom) {
            $custom = Role::whereNotIn('name', ['super_admin', 'admin', 'manager', 'cashier'])
                ->orderBy('name')->pluck('name')->toArray();
            return array_merge($system, $custom);
        }

        return $system;
    }

    public function index()
    {
        $users = User::orderBy('name')->get()
            ->sortBy(fn($u) => match($u->role) {
                'super_admin' => 0, 'admin' => 1, 'manager' => 2, 'cashier' => 3, default => 4
            });
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $allowedRoles = $this->getAllowedRoles();
        $customRoles  = Role::whereNotIn('name', ['super_admin', 'admin', 'manager', 'cashier'])
            ->orderBy('name')->pluck('name')->toArray();
        return view('users.create', compact('allowedRoles', 'customRoles'));
    }

    public function store(Request $request)
    {
        $allowedRoles = $this->getAllowedRoles();

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'role'     => 'required|in:' . implode(',', array_map(fn($r) => '"'.$r.'"', $allowedRoles)),
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        // Re-validate role exists (handles spaces in custom role names)
        if (!in_array($request->role, $allowedRoles)) {
            return back()->withErrors(['role' => 'Invalid role selected.'])->withInput();
        }

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $request->role,
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'User create ho gaya!');
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();

        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            abort(403, 'Super Admin ko sirf Super Admin edit kar sakta hai.');
        }

        $allowedRoles = $this->getAllowedRoles();
        $customRoles  = Role::whereNotIn('name', ['super_admin', 'admin', 'manager', 'cashier'])
            ->orderBy('name')->pluck('name')->toArray();

        return view('users.edit', compact('user', 'allowedRoles', 'customRoles'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();

        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            abort(403, 'Super Admin ko sirf Super Admin edit kar sakta hai.');
        }

        if ($user->id === $currentUser->id && $request->role !== $user->role) {
            return back()->with('error', 'Aap khud ka role change nahi kar sakte.');
        }

        $allowedRoles = $this->getAllowedRoles();

        if (!in_array($request->role, $allowedRoles)) {
            return back()->withErrors(['role' => 'Invalid role selected.'])->withInput();
        }

        $rules = [
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'is_active' => 'boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(6)];
        }

        $validated = $request->validate($rules);
        $validated['role']      = $request->role;
        $validated['is_active'] = $request->boolean('is_active');

        if ($user->id === $currentUser->id) {
            $validated['is_active'] = true;
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'User update ho gaya!');
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Aap khud ko delete nahi kar sakte!');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admin ko delete nahi kiya ja sakta!');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User delete ho gaya!');
    }
}
