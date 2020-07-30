<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use App\User;
use App\Community;
use App\Cmessage;
use Illuminate\Support\Facades\Log;
use DB;
use Storage;

class CommunityController extends Controller
{
    //
    public function add()
      {
        $user = Auth::user();
        $users = $user->mutual_follows();

        return view('user.community.create',['users'=>$users]);
      }

      public function create(Request $request)
        {
          $this->validate($request, [
            'name' => 'required',
            'user_id' => 'required',
            ]);
          $community = new Community;
          $form = $request->name;

          $community->name = $form;

          $me = Auth::user();
          $community->user_id = $me->id;

          if(isset($request['image'])) {

            $path = Storage::disk('s3')->putFile('/',$request['image'],'public');
            $community->image = Storage::disk('s3')->url($path);

          }

          $community->save();

          $user = new User;

          $me->communities()->attach($community->id);
          foreach($request->user_id as $item){

            $user->id=$item;
            $user->communities()->attach($community->id);
          }


          return redirect('home');
        }

        public function edit(Request $request)
        {
          $user = Auth::user();
          $community = Community::find($request->id);

          $members = $community->users()->get();
          $friends = $user->mutual_follows();
          foreach ($members as $member) {
            $cId[] = $member->id;
          }

          foreach ($friends as $friend) {
            $fId[] = $friend->id;
          }

          $users = User::whereIn('id',$fId)->whereNotIn('id',$cId)->get();

          return view('user.community.edit', ['community_form' => $community,'user' => $user, 'members'=>$members, 'users' =>$users]);
        }

        public function update(Request $request)
        {
          $this->validate($request, Community::$rules);
          $community = Community::find($request->id);

          $user = new User;

          if(!empty($request->member_id)){
            foreach($request->member_id as $value){//['member_id']これは連想配列の書き方
              $user->id=$value;
              $user->communities()->detach($community->id);
            }
          }

          if(!empty($request->user_id)){
            foreach($request->user_id as $value){
              $user->id=$value;

              $user->communities()->attach($community->id);
            }
          }

          if(isset($request['image'])) {

            $deleteimg = basename($community->image);
            $disk = Storage::disk('s3');
            $disk->delete('/', $deleteimg);
            $path = Storage::disk('s3')->putFile('/',$request['image'],'public');
            $community->image = Storage::disk('s3')->url($path);
          }

          $community->name = $request->name;

          $community->save();

          return redirect('home');
        }

        public function withdraw(Request $request)
        {
         $community = new Community;
         $community->id = $request->id;
         $user = Auth::user();

         $user->communities()->detach($community->id);

        $communityuser = $community->users()->get();

          if($communityuser->count() == 0){
          $deletecmnt = Community::find($community->id);
          $deleteimg = basename($deletecmnt->image);
          $disk = Storage::disk('s3');
          $disk->delete('/', $deleteimg);

          $deletemsg = Cmessage::where('community_id',$community->id);
          $msgimg = Cmessage::where('community_id',$community->id)->get();
          foreach($msgimg as $item){
              Log::debug($item);
              $deleteimg = basename($item->image_path);
              Log::debug($msgimg);
              $disk = Storage::disk('s3');
              $disk->delete('/', $deleteimg);
            }

          $deletemsg->delete();

          $deletecmnt->delete();
          }

         return redirect('home');
        }

        public function talk(Request $request)
        {
          $community = Community::find($request->id);

          $message = new Cmessage;
          $count = $message->where('community_id',$request->id)->count();

          return view('user.community.talk',['community' => $community]);
        }

        public function getMessageC(Request $request)
        {
          $id = $request->input('id');
          $messageRecords = Cmessage::where('community_id',$id)->get();

          $messages = [];

          foreach($messageRecords as $messageRecord)
          {
            $item = [
              'name' => $messageRecord->user->name,
              'message' => $messageRecord->message,
              'image' => $messageRecord->image_path,
              'created_at' => $messageRecord->created_at->format('Y/m/d H:s')
              ];

            $messages[] = $item;
          }
          //モデルの関連づけメソッドは()いらない。✖︎user()

          $json = ["messages" => $messages];

          return response()->json($json);
        }

        public function sendC(Request $request)
      {
        $upfile = $request;
        // Log::info(var_dump($request));
        $this->validate($request, ['message'=>'required']);
        $user = Auth::user();
        $message = new Cmessage;

        $message->user_id = $user->id;

        $message->community_id = $_POST['community_id'];

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

        $count = $message->where('community_id',$_POST['community_id'])->count();
          if ($count > 50) {
            $message50 = DB::table('cmessages')
            ->where('community_id', $_POST['community_id'])
            ->orderBy('id','desc')
            
            ->take(50);
            $deleteid = $message50->pluck('id')->min();

            $messageins = Cmessage::where('community_id',$_POST['community_id']);
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

}
