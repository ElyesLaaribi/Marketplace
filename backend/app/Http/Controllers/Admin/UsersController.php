<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class UsersController extends Controller
{
    /**
     * Handle the incoming request.
     */
    // public function __invoke(Request $request)
    // {
        
    //     $user = User::first();

    //     return new UserResource($user);
    // }

    public function index()
    {
        return UserResource::collection(User::all());
    }

    public function show(User $user)
    {
        return UserResource::make($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }
}
