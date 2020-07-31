<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Follow;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Group;
use App\Message;
use Illuminate\Support\Facades\Log;
use Storage;

class GroupController extends Controller
{
    //
  public function add()
    {
      $user = Auth::user();
      $users = $user->mutual_follows();

      return view('user.group.create',['users'=>$users]);
    }

  public function create(Request $request)
    {
      $this->validate($request, [
        'name' => 'required',
        'user_id' => 'required',
        ]);
      $group = new Group;
      $form = $request->name;

      $group->name = $form;

      $me = Auth::user();
      $group->user_id = $me->id;

      if(isset($request['image'])) {
        $path = Storage::disk('s3')->putFile('/',$request['image'],'public');
        $group->image = Storage::disk('s3')->url($path);
      }

      $group->save();

      $user = new User;

      $hash = Hash::make($me->name);
      $nickname = substr($hash,-10);

      $me->groups()->attach($group->id, ['nickname' => $nickname]);

      foreach($request->user_id as $item){

        $user->id=$item;
        $member = User::find($item);
        $hashn = Hash::make($member->name);
        $nickn = substr($hashn,-10);
        $user->groups()->attach($group->id,['nickname' => $nickn]);
      }
      //66行目'nickname'同じだからエラーくるかも？

      return redirect('home');
    }

  public function talk(Request $request)
  {
    $group = Group::find($request->id);

    return view('user.group.talk',['group' => $group]);
  }

    public function message()
  {
    $group = Group::find(1);

    return view('user.group.chat',['group' => $group]);
  }

  public function send(Request $request)
  {
    $this->validate($request, ['message'=>'required']);
    $user = Auth::user();
    $message = new Message;
    $form = $request->all();
    if (isset($form['image'])) {
      $path = $request->file('image')->store('public/image');
      $message->image_path = basename($path);
    } else {
      $message->image_path = null;
    }

    unset($form['_token']);

    unset($form['image']);

    $message->user_id = $user->id;
    $message->fill($form);
    $message->save();

    return redirect()->action('User\GroupController@talk',['id'=> $request->group_id]);//この行がリロードを引き起こす。
  }

  public function getMessage(Request $request)
  {

    $id = $request->input('id');

    $messageRecords = Message::where('group_id',$id)->get();
    $group = Group::find($id);
    $members = $group->users()->get();
    foreach($members as $member){
      $memId[] = $member->id;
      $nName[] = $member->pivot->nickname;
    }
    $nickname = array_combine($memId,$nName);

    $messages = [];

    foreach($messageRecords as $messageRecord)
    {

      $name = $nickname[$messageRecord->user_id];

      $item = [
        'name' => $name,
        'message' => $messageRecord->message,
        'image' => $messageRecord->image_path,
        'created_at' => $messageRecord->created_at->format('Y/m/d H:s')
        ];
      $messages[] = $item;
    }
    //モデルの関連づけメソッドは()いらない。✖︎user()
    asort($nName);
    $json = ["messages" => $messages, "names" => $nName];
    return response()->json($json);
  }

  public function sendM(Request $request)
  {
    $this->validate($request, ['message'=>'required']);
    $user = Auth::user();
    $message = new Message;

    $message->user_id = $user->id;

    $message->group_id = 1;

     ini_set('display_errors','no');
      if($_POST){
      	$messagef = $_POST['message'];
      	$message->message = $messagef;
      	Log::debug($messagef);
      	$message->save();
      }
  }

    public function sendC(Request $request)
  {

    $upfile = $request;

    $this->validate($request, ['message'=>'required']);
    $user = Auth::user();
    $message = new Message;

    $message->user_id = $user->id;

    $message->group_id = $_POST['group_id'];

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

    $count = $message->where('group_id',$_POST['group_id'])->count();
          if ($count > 50) {
            $message50 = DB::table('messages')
            ->where('group_id', $_POST['group_id'])
            ->orderBy('id','desc')

            ->take(50);
            $deleteid = $message50->pluck('id')->min();

            $messageins = message::where('group_id',$_POST['group_id']);
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

  public function edit(Request $request)
  {
    $user = Auth::user();
    $group = Group::find($request->id);
    $members = $group->users()->get();

    foreach($members as $member){
      $membersnames[] = $member->pivot->nickname;
      $membersnames[] = $member->pivot->user_id;
      Log::debug($membersnames);
    }

    $users = $user->mutual_follows();

    return view('user.group.edit', ['group_form' => $group,'user' => $user, 'members'=>$members, 'users' =>$users]);
  }

  public function update(Request $request)
  {
    $this->validate($request, Group::$rules);
    $group = Group::find($request->id);

    $user = new User;//ユーザーを新しく作成する時に使うのが、このインスタンス化のコード。

    if(!empty($request->member_id)){
      foreach($request->member_id as $value){//['member_id']これは連想配列の書き方。
        $user->id=$value;
        $user->groups()->detach($group->id);
      }
    }

    if(!empty($request->user_id)){
      foreach($request->user_id as $value){
        $user->id=$value;

        $user->groups()->attach($group->id);
      }
    }

    $group->name = $request->name;

    $group->save();

    return redirect('home');
  }

  public function withdraw(Request $request)
  {
   $group = new Group;
   $group->id = $request->id;
   $user = Auth::user();

   $user->groups()->detach($group->id);

   $groupuser = $group->users()->get();

    if($groupuser->count() == 0){
      $deletegrp = Group::find($group->id);

      $deleteimg = basename($deletegrp->image);
      $disk = Storage::disk('s3');
      $disk->delete('/', $deleteimg);

      $deletemsg = Message::where('group_id',$group->id);
      $msgimg = Message::where('group_id',$request->id)->get();

        foreach($msgimg as $item){

              Log::debug($item);
              $deleteimg = basename($item->image_path);
              Log::debug($msgimg);
              $disk = Storage::disk('s3');
              $disk->delete('/', $deleteimg);

            }


      $deletemsg->delete();
      $deletegrp->delete();
    }

  return redirect('home');
  }
}
