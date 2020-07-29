<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use App\User;
use Abraham\TwitterOAuth\TwitterOAuth;
use Storage;

class ProfileController extends Controller
{
    //
  public function profile()
  {
    $user = Auth::user();

    return view('user.profile.profile',['user' => $user]);
  }

  public function edit()
  {
    $user = Auth::user();

    return view('user.profile.edit',['user'=>$user]);
  }

  public function update(Request $request)
  {
    $user = Auth::user();

    if(!empty($request->name)){
      $user->name = $request->name;
    }

    if(!empty($request->text)){
      $user->text = $request->text;
    }

    if(isset($request['avatar'])) {
      $deleteavt = basename($user->avatar);
      $disk = Storage::disk('s3');
      $disk->delete('/', $deleteavt);
      $path = Storage::disk('s3')->putFile('/',$request['avatar'],'public');
      $user->avatar = Storage::disk('s3')->url($path);
    }

    $user->save();
    return redirect('/');
  }

  public function delete()
  {

  }
}
