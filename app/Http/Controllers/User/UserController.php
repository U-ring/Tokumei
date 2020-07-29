<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Umessage;
use Illuminate\Support\Facades\Log;
use Storage;

class UserController extends Controller
{
    //
  public function index(Request $request)
  {
      $me = Auth::user();
      $friends = $me->mutual_follows();

      $cond_name = $request->cond_name;

      if ($cond_name !='') {
        $users = User::where('name', $cond_name)->get();

      }else {

       $users = null;

      }

      return view('user.user.index', ['friends' => $friends, 'users' => $users, 'cond_name' => $cond_name]);
  }

  public function talk(Request $request)
  {
    $user = User::find($request->id);

    return view('user.user.talk',['user' => $user]);
  }

  public function getMessageU(Request $request)
  {

    $id = $request->input('id');

    $query = Umessage::query();
    $query->where('user_id',Auth::id());
    $query->where('talk_user_id',$id);
    $umessages = $query->get();

    $messages = [];

    foreach($umessages as $umessage)
    {
      $item = [
        'name' => $umessage->user->name,
        'message' => $umessage->message,
        'image' => $umessage->image_path,
        'created_at' => $umessage->created_at->format('Y/m/d H:s')
        ];

      $messages[] = $item;
    }

    $json = ["messages" => $messages];
    // dd($json);
    return response()->json($json);
  }

  public function sendC(Request $request)
{

  $upfile = $request;

  $this->validate($request, ['message'=>'required']);
  $user = Auth::user();
  $message = new Umessage;

  $message->user_id = $user->id;

  $message->talk_user_id = $_POST['user_id'];

  $messagef = $_POST['message'];
  $message->message = $messagef;

  $form = $request->all();

  if (isset($form['image'])) {

    $path = Storage::disk('s3')->putFile('/',$request['image'],'public');
    $message->image_path = Storage::disk('s3')->url($path);

  } else {
    $message->image_path = null;
  }

  $message->save();

  $count = $message->where('talk_user_id',$_POST['user_id'])->count();
          if ($count > 50) {
            $message50 = DB::table('umessages')
            ->where('talk_user_id', $_POST['user_id'])
            ->orderBy('id','desc')
            
            ->take(50);
            $deleteid = $message50->pluck('id')->min();

            $messageins = umessage::where('talk_user_id',$_POST['user_id']);
            $deletemessage = $messageins->where('id','<',$deleteid);

            $image = $deletemessage->pluck('image_path');

            foreach($image as $item){
              if($item !== null){
                $item = basename($item);
                $disk = Storage::disk('s3');
                $disk->delete('/', $item);
              }
            }

            $deletemessage->delete();
          }
}

  public function edit()
  {
    return view('user.user.edit');
  }

  public function Terms()
  {
    return view('user.user.TermsOfService');
  }
}
