<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard',[
            'totalAdmin' => User::where('role','admin')->count(),

            'totalUser' => User::count(),

            'apiStatus' => 'Offline',

            'cctvTotal' => 0,
        ]);
    }
}