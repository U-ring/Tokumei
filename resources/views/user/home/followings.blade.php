@extends('layouts.home')

@section('title.フォロー一覧')

@section('content')
@if($users)
<div class="p-10 row">
 <p class="display-5 font-weight-bold py-4 mx-auto">フォローが返ってきませんか？</p>
</div>
  <div class="row d-flex justify-content-between p-4">
      <table>
        <tr>
          <th></th>
          <th></th>
          <th></th>
        </tr>
        <tbody>
          @foreach($users as $user)
           @if(isset($user->avatar))
           <tr>
            <td><div class="rounded-circle shadow-lg p-1"><img src="{{ asset('storage/image/' . $user->avatar) }}" class="rounded-circle" width="50" height="50"></div></td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->text }}</td>
            <td><div>@include('parts.follow_button',['user'=>$user])</div></td>
           </tr>
           @elseif(!isset($user->avatar))
           <tr>
            <td><div class="rounded-circle shadow-lg p-1"><img src="{{ asset('storage/image/' . 'PpnG7mWNSuOG4o4JZn2VItzkk7vIt9zLVk3zJybe.jpeg') }}" class="rounded-circle" width="50" height="50"></div></td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->text }}</td>
            <td><div>@include('parts.follow_button',['user'=>$user])</div></td>
           </tr>
           @else
          @endif
          @endforeach
        </tbody>
      </table>
  </div>
</div>
@else
<div class="p-10 row">
 <p class="display-5 font-weight-bold py-4 mx-auto">一方的なフォローはありません。</p>
</div>
@endif
@endsection
