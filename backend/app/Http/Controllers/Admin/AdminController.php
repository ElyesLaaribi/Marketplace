<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;

class AdminController extends Controller
{
    public function index()
    {
        $currentUserId = auth('admin')->id();

        $admins = Admin::where('id', '!=', $currentUserId)->get();

        return AdminResource::collection($admins);
    }

    public function show(Admin $admin)
    {
        return AdminResource::make($admin);
    }
    
    public function destroy(Admin $admin)
    {
        $admin->delete();

        return response()->noContent();
    }
}
