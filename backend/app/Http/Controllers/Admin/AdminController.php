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
        return AdminResource::collection(Admin::all());
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
