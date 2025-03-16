<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Http\Controllers\Controller;

class AdminLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422); 
        }
    

        $admin = Admin::where('email', $request->email)->first();
    
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Unauthorized, check your login credentials',
                'errors' => [
                    'email' => ['The provided email does not match our records.'],
                    'password' => ['The provided password is incorrect.']
                ]
            ], 401); 
        }
  
        $tokenResult = $admin->createToken('Admin Personal Access Token');
        $token = $tokenResult->plainTextToken;
    
        return response()->json([
            'token' => $token,
            'admin' => $admin
        ]);
    }
}
