<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
// use App\Http\Controllers\BaseController as ControllersBaseController;
use Illuminate\Support\Facades\Auth;

// use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
         if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $authUser = Auth::user(); 
            $token = $authUser->createToken('API Token')->plainTextToken;
            $success['name'] =  $authUser->name;
   
            return response()->json(['token' => $token,'user' => $authUser], 200);
        } 
        else{ 
            return response()->json(['message' => 'Invalid credentials'], 401);
        } 
    }
    
}
