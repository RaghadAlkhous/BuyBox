<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetCode(Request $request){
        $request->validate([
            'email'=>'required|email|exists:users,email'
        ]);
        $email=$request->email;
        $code=Str::random(6);
//        User::updateOrInsert(['email'=>$email,
//            'password_reset_codes'=>$code
//        ]);
        $user = User::where('email', $request->email)->first();
        $user->password_reset_codes=$code;
        $user->save();

        Mail::raw("Your password reset code is: $code", function ($message) use ($email) {
            $message->to($email)
                ->subject('Password Reset Code');
        });
        return response(['message' => 'Reset code sent to your email.'],200);
    }
    public function verifyResetCode(Request $request){
        $request->validate([
            'email'=>'required|email|exists:users,email',
            'code'=>'required|string'
        ]);
        $currentCode=User::where('email',$request->email)->
            where( 'password_reset_codes',$request->code)->first();
        if ( $currentCode->password_reset_codes !== $request->code ||$currentCode->updated_at<now()->subMinutes(15)){
            return response(['message' => 'Invalid or expired reset code.'], 400);
        }
        return response(['message'=>'the code is correct'],200);


    }
    public function resetPassword(Request $request){
        $request->validate([
            'email'=>'required|email|exists:users,email',
            'password'=>'required|confirmed|min:8|max:30'
        ]);
        $user=User::where('email',$request->email)->first();
        $user->password=$request->password;
        $user->save();
        return response(['message' => 'Password has been reset.'],200);

    }



}
