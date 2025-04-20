<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Pengumuman;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all(); // Fetch all users from the users table
        
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        
        return view('beranda.homeSuperAdmin', compact('users', 'pengumuman'));
    }

    public function indexUser()
    {
        $users = User::all();
        
        return view('admin.adduserAdmin', compact('users'));
    }
}
