<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use App\Models\User;
use App\Models\Pengumuman;
use App\Models\Dosen;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        return view('beranda.homeAdmin', compact('pengumuman'));
    }

    public function indexUser()
    {
        $this->authorize('viewAny', User::class);
        $users = User::with(['mahasiswa', 'dosen', 'konselor', 'kemahasiswaan', 'keasramaan', 'orangTua'])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->get();
        return view('admin.users.index', compact('users'));
    }
    
    public function create()
    {
        $this->authorize('create', User::class);
        $dosen = Dosen::select('username', 'nama', 'nip')->get();
        return view('admin.users.create', compact('dosen'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        try {
            Log::info('Attempting to create user with data: ', $request->validated());
            $this->userService->createUserWithRole($request->validated());
            return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage(), ['data' => $request->validated()]);
            return back()->withInput()->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $dosen = Dosen::select('username', 'nama', 'nip')->get();
        return view('admin.users.edit', compact('user', 'dosen'));
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        try {
            $this->userService->updateUser($user, $request->validated());
            return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui user. Silakan coba lagi.');
        }
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        try {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }
}