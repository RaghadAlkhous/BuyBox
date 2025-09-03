<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegesterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegesterRequest $request)
    {
        $validated = $request->validated();
    
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('UserImages'), $imageName);
            $path = 'UserImages/' . $imageName;
        }
        $user = User::create($request->safe()->except('image'));
        $user->image = $path;
        $user->save();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response([
            'message' => 'The User Registered Successfully', 
            'token' => $token,
            'image_url' => $path ? url($path) : null], 201);
            
    }

    public function login(Request $request)
    {
        $user = User::where('phone_number', $request->phone_number)->first();
        
        if (!$user) {
            return response(['message' => 'The Phone Number Incorrect'], 404);
        }
    
        if (!Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
            return response(['message' => 'Password Incorrect'], 400);
        }
    
        $token = $user->createToken('auth_token')->plainTextToken;
        return response(['token' => $token, 'message' => 'The User Logged in Successfully'], 200);
    }
    

    public function logout(Request $request)
    {
        $token = $request->user()->tokens()->delete();
        return response(['token'=>$token,'message'=>'The User Logged out Successfully'],200);
    }
}
