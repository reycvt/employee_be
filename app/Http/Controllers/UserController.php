<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
// use App\Http\Controllers\BaseController as ControllersBaseController;

use Illuminate\Support\Facades\Hash;

// use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{ 
    public function index(){
        $data_user= User::all();
        return response()->json(['message'=>$data_user],200);
    }
public function show($id){
    $data_user = User::find($id);
    if($data_user){
        return response()->json(['data user'=>$data_user],200);
    }
    else{
        return response()->json(['message'=>'data tidak di temukan'],404 );
    }
}
public function update(Request $request,$id){
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
    ]);

    $data_user = User::find($id);
    if($data_user){
    $data_user->name = $request->name;
    $data_user->email=$request->email;
    $data_user->password=$request->password;
    $data_user->update();

    return response()->json([['message'=>'berhasil di update'],200]);
    }
    else{
        return response()->json(['message'=>'data tidak di temukan'],404 );
    }
}
    
}
