<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.index', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status', true)->count(),
            'totalRoles' => Role::count(),
            'latestUsers' => User::latest()->take(5)->get(),
        ]);
    }
}
