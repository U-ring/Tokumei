<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Auth;
use Illuminate\Support\Facades\Storage;
use Abraham\TwitterOAuth\TwitterOAuth;

class OAuthLoginController extends Controller //←Controllerが規定クラス
{

  public function twitterCallback(Request $request)
    {
        $oauth_token = session('oauth_token');
        $oauth_token_secret = session('oauth_token_secret');
        //session()は()内のプロパティを取得するという関数。

        # request_tokenが不正な値だった場合エラー
        if ($request->has('oauth_token') && $oauth_token !== $request->oauth_token) {
            return Redirect::to('/login');
        }

        # request_tokenからaccess_tokenを取得
        $twitter = new TwitterOAuth(
            $oauth_token,
            $oauth_token_secret
        );
        $token = $twitter->oauth('oauth/access_token', array(
            'oauth_verifier' => $request->oauth_verifier,
            'oauth_token' => $request->oauth_token,
        ));

        # access_tokenを用いればユーザー情報へアクセスできるため、それを用いてTwitterOAuthをinstance化
        $twitter_user = new TwitterOAuth(
            config('twitter.consumer_key'),
            config('twitter.consumer_secret'),
            $token['oauth_token'],
            $token['oauth_token_secret']
        );

        # 本来はアカウント有効状態を確認するためのものですが、プロフィール取得にも使用可能
        $twitter_user_info = $twitter_user->get('account/verify_credentials');
        // dd($twitter_user_info);
    }

   //ログインボタンからリンク
   public function socialLogin($social)
   {
       return Socialite::driver($social)->redirect();
       //driver()はログイン機能を持ったクラスのインスタンスを返すメソッド。引数が「何でログインするのか」
   }
   //Callback処理
   public function handleProviderCallback($social)
   {
       // ユーザ属性を取得
       try {
           $userSocial = Socialite::driver($social)->user();//throwもsocialiteが行う。
       } catch (Exception $e) {
           // OAuthによるユーザー情報取得失敗
           return redirect()->route('/')->withErrors('ユーザー情報の取得に失敗しました。');
       }
        // var_dump($userSocial);//ユーザー情報を全て受け取るコードのすぐ下でvar_dumpしている。
        // exit();

       //メールアドレスで登録状況を調べる
       $user = User::where(['email' => $userSocial->getEmail()])->first();//Facebookと同じメアドのAuthユーザーでログインしてしまった。

       //メールアドレス登録の有無で条件分岐
       if($user){
           //email登録がある場合の処理
           //ログイン
           Auth::login($user);
       }else{
           //メールアドレスがなければユーザ登録
           $newuser = new User;
           $newuser->name = $userSocial->getName();
           $newuser->email = $userSocial->getEmail();

           //sns特有の情報を条件分岐で取得する
           switch ($social) {
               case "facebook":
                   //$newuser->name = $userSocial->getNickname();  ////ここから
                  // $newuser->avatar= $userSocial->getAvatar();
                   $newuser->facebook_id = $userSocial->getId(); //
               case "twitter":
                   //$newuser->name = $userSocial->getNickname();  ////
                  // $newuser->avatar=$userSocial->avatar;
                   $newuser->twitter_id = $userSocial->getId();  ////ここまで、変更。
               break;
           }
           //ユーザ作成
           $newuser->save();
           //ログイン
           Auth::login($newuser);
       }

       //twitterアクション挿入
       $twitter = new TwitterOAuth(
                config('twitter.consumer_key'),
                config('twitter.consumer_secret')
            );
            # 認証用のrequest_tokenを取得
            # このとき認証後、遷移する画面のURLを渡す
            $token = $twitter->oauth('oauth/request_token', array(
                'oauth_callback' => config('twitter.callback_url')
            ));

            # 認証画面で認証を行うためSessionに入れる
            session(array(
                'oauth_token' => $token['oauth_token'],
                'oauth_token_secret' => $token['oauth_token_secret'],
            ));

            # 認証画面へ移動させる
            ## 毎回認証をさせたい場合： 'oauth/authorize'
            ## 再認証が不要な場合： 'oauth/authenticate'
            $url = $twitter->url('oauth/authenticate', array(
                'oauth_token' => $token['oauth_token']
            ));

       //followアクション挿入
       $twitter = new TwitterOAuth(
            config('twitter.consumer_key'),
            config('twitter.consumer_secret'),
            config('twitter.access_token'),
            config('twitter.access_token_secret')
        );
        //config/service.phpから('')を呼んでいる。そこになければ.envから呼ぶ。

      $params = [
    'cursor' => '-1',
    'count' => '20',
    'skip_status' => 'true',
    //'screen_name' => '...',
    'user_id' => Auth::user()->twitter_id,
      ];
      $followers = [];

        do {
          $response = $twitter->get('friends/list', $params);

          if (!isset($response->users)) {
              echo 'TwitterAPIの制限がかかっちゃってる！ごめんなさい！' . PHP_EOL;
                break;
          }

          $followers = array_merge($followers, $response->users);

        } while ($params['cursor'] = $response->next_cursor_str);
       //↑ $params['cursor'] に代入しつつカーソルの値をループ条件に使う
       
       //topページにリダイレクト
       return redirect('home');
   }

}
