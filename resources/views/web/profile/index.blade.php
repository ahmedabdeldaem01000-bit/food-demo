@extends('web.app')
@section('page-title')
    Profile
@endsection
@section('content')
    <div class="flex flex-col gap-4 items-center h-screen justify-center content-center w-full ">
<div class="flex flex-wrap flex-row items-center gap-4">
    <!-- <img src="{{ asset('storage/images/user/'.$data->photo) }}" class="w-16 rounded-full " alt="{{ $data->name }}" /> -->
  <div class="flex flex-col items-center">
                    <img src="{{ asset(auth()->user()->photo?? asset('images/default.png')) }}"
                        class="w-24 h-24 rounded-full object-cover border-4 border-white shadow">

                    <h3 class="mt-3 text-lg font-semibold">
                        {{ auth()->user()->name }}
                    </h3>
                </div>
</div>
        <div class=" gap-5 flex justify-center flex-col">
           <a href="{{ route('profile.personalEditShow') }}"> <ul class="flex flex-wrap flex-row items-center justify-between content-center gap-2">
                <li><i class="fa-regular fa-circle-user"></i></li>
                <li>Personal information</li>
                <li><i class="fa-solid fa-arrow-right-from-bracket"></i></li>
            </ul></a>
     
            <a href="{{ route('profile.showOrders') }}">
                <ul class="flex flex-wrap flex-row items-center justify-between content-center gap-2">
                <li><i class="fa-solid fa-utensils"></i></li>
                <li>My orders</li>
                <li><i class="fa-solid fa-arrow-right-from-bracket"></i></li>
            </ul>
            </a>
            
            <ul class="flex flex-wrap flex-row items-center justify-between content-center gap-2">
                <li><i class="fa-solid fa-arrow-right-from-bracket"></i></li>
                <li>Log out</li>
                <li><i class="fa-solid fa-arrow-right-from-bracket"></i></li>
            </ul>

        </div>

    </div>
@endsection