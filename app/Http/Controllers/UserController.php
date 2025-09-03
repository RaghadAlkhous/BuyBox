<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use function Symfony\Component\String\u;

class UserController extends Controller
{
    public function getProfile(UserRequest $request){
        $user = $request->user(); // استخراج المستخدم من التوكن

        if (!$user) {
           return response(['message'=>'User Not Found'],404);

       }
        return response(['user'=>UserResource::make($user)],200);

    }
    public function updateProfile(UserRequest $request){
        $user = $request->user(); // استخراج المستخدم من التوكن


        if (!$user){
            return response(['message'=>'User Not Found'],404);
        }
        if ($request->image)
        {
            if ($user->image && File::exists(public_path($user->image))) {
                File::delete(public_path($user->image));
            }
            $imageName= time().'.'.$request->image->extension();
            $request->image->move(public_path('UserImages'), $imageName);

            $user->update(['image'=>'UserImages/' . $imageName]);
            $user->save();
        }
        $user->update($request->safe()->except('image'));

        return response(['user'=>UserResource::make($user)],200);
    }
    public function changePassword(Request $request){
        $request->validate([
            'current_password'=>'required|min:8|max:30',
            'new_password'=>'required|confirmed|min:8|max:30'
        ]);
        $user=Auth::user();
        if(!Hash::check($request->current_password,$user->password)){
            return response(['message'=>'Current password is incorrect.'],400);
        }
        $user->password=$request->new_password;
        $user->save();
        return response(['message'=>'Password changed successfully.'],200);
    }
    public function forgotPassword(){
       $user=Auth::user();
       $email=$user->email;
       $code=Str::random(6);
       $user->update(['password_reset_codes'=>$code]);
       $user->save();

       Mail::raw("Your password reset code is: $code", function ($message) use ($email) {
            $message->to($email)->subject('Password Reset Code');
        });
        return response(['message'=>'Reset code sent to your email.'],200);

    }
    public function resetPassword(Request $request){
        $request->validate([
            'code'=>'required|string',
            'new_password'=>'required|confirmed|min:8|max:30'
        ]);
        $user=Auth::user();
        $currentCode=$user->password_reset_codes;
//        dd($currentCode);
        if($currentCode != $request->code||$user->updated_at< now()->subMinutes(15)){
            return response(['message'=>'Invalid or expired reset code.'],400);
        }

        $user->password=$request->new_password;
        $user->password_reset_codes = null;
        $user->save();
        return response(['message'=>'Password has been reset.'],200);

    }
    public function getProfileById(UserRequest $request, $id){
        $user = User::find($id); 
        
        if (!$user) {
            return response(['message' => 'User Not Found'], 404);
        }
    
        return response([
            'user' => UserResource::make($user),
            'phone_number' => $user->phone_number // استخدام الهاتف بدلاً من phone
        ], 200); 
    }
}
