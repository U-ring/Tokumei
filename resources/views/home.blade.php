@extends('layouts.home')

@section('title.hello')

@section('content')
  <div class="p-10">
   <p class="display-4 font-weight-bold pt-3 col-sm-12">ようこそ、 </p>
   <p class="display-4 font-weight-bold col-sm-12">{{ Auth::user()->name }}さん！</p>
   <p class="lead px-4 pt-4">あなたにとって本質的で重要なことであればあるほど、みんなは直接言いづらい。</p>
   <p class="lead px-4 pb-4">匿名会議を使ってコミュニケーションを効率化しませんか？</p>
  </div>
  <div class="row"><p class="font-weight-bold p-1 mx-auto">匿名グループ</p></div>
  <div class="d-flex justify-content-between flex-wrap">
       @foreach($groups as $group)
        @if(isset($group->image))
         <div class="d-flex flex-row pb-3">
          <table>
           <thead>
            <tr>
             <th></th>
             <th></th>
             <th></th>
            </tr>
           </thead>
           <tr>
            <td>
            <div class="my-auto mx-1" width="25%">
             <div class="my-auto" style="width:60px;height:60px;">
              <div class="rounded-circle shadow-lg p-1">
               <img src="{{ $group->image }}" class="rounded-circle" width="50" height="50">
              </div>
             </div>

           </div>
            </td>
            <td>
              <div style="width:50px;">
                <h class="font-weight-bold">{{ $group->name }}</h>
              </div>
            </td>
            <td>
             <div class="d-flex flex-column px-2">

              <div>
                <form action="{{ action('User\GroupController@withdraw')}}" method="post">
                @csrf
                <button type="submit" name="id" value="{{ $group->id }}" class="my-3 btn btn-danger">退会</button>
                {{-- {{ csrf_field() }} --}}
                </form>
              </div>
              <div><a class="btn btn-primary my-3" href="{{ action('User\GroupController@talk', ['id' => $group->id] ) }}">トーク</a></div>
             </div>
            </td>
           </tr>
          </table>
         </div>
        @elseif(!isset($group->image))
         <div class="d-flex flex-row pb-3">
          <table>
           <thead>
            <tr>
             <th></th>
             <th></th>
             <th></th>
            </tr>
           </thead>
           <tr>
            <td>
            <div class="my-auto mx-1" width="25%">
             <div class="my-auto" style="width:60px;height:60px;">
              <div class="rounded-circle shadow-lg p-1">
               @include('parts.nonAvatar')
              </div>
             </div>

           </div>
            </td>
            <td>
              <div style="width:50px;">
                <h class="font-weight-bold">{{ $group->name }}</h>
              </div>
            </td>
            <td>
             <div class="d-flex flex-column px-2">

              <div>
               <form action="{{ action('User\GroupController@withdraw')}}" method="post">
                 @csrf
                 <button type="submit" name="id" value="{{ $group->id }}" class="m-3 btn btn-danger">退会</button>
                 {{-- {{ csrf_field() }} --}}
               </form>
             </div>
             <div><a class="btn btn-primary m-3" href="{{ action('User\GroupController@talk', ['id' => $group->id] ) }}">トーク</a></div>
             </div>
            </td>
           </tr>
          </table>
         </div>
        @else
       @endif
       @endforeach
  </div>
  <div class="row blank-top">
    <p class="font-weight-bold p-1 mx-auto">
      コミュニティ
    </p>
  </div>
  <div class="d-flex justify-content-between flex-wrap">
       @foreach($communities as $community)
        @if(isset($community->image))
         <div class="d-flex flex-row pb-3">
          <table>
           <thead>
            <tr>
             <th></th>
             <th></th>
             <th></th>
            </tr>
           </thead>
           <tr>
            <td>
            <div class="my-auto mx-1" width="25%">
             <div class="my-auto" style="width:60px;height:60px;">
              <div class="rounded-circle shadow-lg p-1">
               <img src="{{ $community->image }}" class="rounded-circle" width="50" height="50">
              </div>
             </div>
           </div>
            </td>
            <td>
              <div style="width:50px;">
                <h class="font-weight-bold">{{ $community->name }}</h>
              </div>
            </td>
            <td>
             <div class="d-flex flex-column px-2">

              <div>
                <a class="btn btn-primary m-3" href="{{ action('User\CommunityController@edit', ['id' => $community->id]) }}">編集</a>
              </div>
              <div><a class="btn btn-primary m-3" href="{{ action('User\CommunityController@talk', ['id' => $community->id] ) }}">トーク</a></div>
             </div>
            </td>
           </tr>
          </table>
         </div>
        @elseif(!isset($community->image))
         <div class="d-flex flex-row pb-3">
          <table>
           <thead>
            <tr>
             <th></th>
             <th></th>
             <th></th>
            </tr>
           </thead>
           <tr>
            <td>
            <div class="my-auto mx-1" width="25%">
             <div class="my-auto" style="width:60px;height:60px;">
              <div class="rounded-circle shadow-lg p-1">
               @include('parts.nonAvatar')
              </div>
             </div>
           </div>
            </td>
            <td>
              <div style="width:50px;">
                <h class="font-weight-bold">{{ $community->name }}</h>
              </div>
            </td>
            <td>
             <div class="d-flex flex-column px-2">
               <div>
                 <a class="btn btn-primary m-3" href="{{ action('User\CommunityController@edit', ['id' => $community->id]) }}">編集</a>
               </div>
               <div><a class="btn btn-primary m-3" href="{{ action('User\CommunityController@talk', ['id' => $community->id] ) }}">トーク</a></div>
             </div>
            </td>
           </tr>
          </table>
         </div>
        @else
       @endif
       @endforeach
  </div>
    <div class="row blank-top col-md-10 mx-auto">
      <a class="btn btn-primary m-3" href="/user/group/create">グループ作成</a>
      <a class="btn btn-primary m-3" href="/user/community/create">コミュニティ作成</a>
    </div>　
  <div class="row m-4"><a class="btn btn-primary m-3" href="/logout">ログアウト</a></div>
@endsection
