<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where(function ($q) {
                $q->whereIn('role', ['admin', 'super_admin', 'admin_koperasi', 'admin_kependudukan'])
                  ->orWhere('role', 'like', 'admin%');
            })
            ->whereNull('kdmp_role')
            ->where(function ($q) {
                $q->whereNull('service_slug')
                  ->orWhere('service_slug', '!=', 'kdmp');
            })
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'role', 'service_slug', 'created_at']);

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'currentUserId' => auth()->id(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function store(StoreAdminRequest $request)
    {
        $role = $request->role;
        $serviceSlug = $request->service_slug ? trim($request->service_slug) : null;

        if ($role === 'super_admin') {
            $serviceSlug = null;
        } elseif ($serviceSlug === 'kependudukan' || $role === 'admin_kependudukan') {
            $role = 'admin_kependudukan';
            $serviceSlug = 'kependudukan';
        } elseif ($serviceSlug === 'koperasi' || $role === 'admin_koperasi') {
            $role = 'admin_koperasi';
            $serviceSlug = 'koperasi';
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'service_slug' => $serviceSlug,
        ]);

        return redirect()->route('admin.users')->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->only(['id', 'name', 'email', 'role', 'service_slug']),
        ]);
    }

    public function update(UpdateAdminRequest $request, User $user)
    {
        $role = $request->role;
        $serviceSlug = $request->service_slug ? trim($request->service_slug) : null;

        if ($role === 'super_admin') {
            $serviceSlug = null;
        } elseif ($serviceSlug === 'kependudukan' || $role === 'admin_kependudukan') {
            $role = 'admin_kependudukan';
            $serviceSlug = 'kependudukan';
        } elseif ($serviceSlug === 'koperasi' || $role === 'admin_koperasi') {
            $role = 'admin_koperasi';
            $serviceSlug = 'koperasi';
        }

        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'service_slug' => $serviceSlug,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Admin berhasil dihapus.');
    }
}