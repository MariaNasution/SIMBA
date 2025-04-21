<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use App\Models\User;
use App\Models\Pengumuman;

class AdminController extends Controller
{
    public function index()
    {     
        $users = User::all();

        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        
        return view('beranda.homeAdmin', compact( 'pengumuman', 'users'));
    }

    public function indexUser()
    {
        $this->authorize('viewAny', User::class);
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request, UserService $userService)
    {
        $this->authorize('create', User::class);
        $userService->createUserWithRole($request->validated());
        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('admin.users.edit', compact('user'));
    }

    public function update(StoreUserRequest $request, User $user, UserService $userService)
    {
        $this->authorize('update', $user);
        $userService->updateUser($user, $request->validated());
        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
    }
}
