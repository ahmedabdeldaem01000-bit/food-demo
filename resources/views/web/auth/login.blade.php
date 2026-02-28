@extends('web.app')
@section('content')

<form action="{{ route('login.submit') }}" method="POST" class="p-4 flex flex-col flex-wrap content-center justify-center w-full items-start md:w-100 md:border md:border-green-800 md:rounded-[15px] md:justify-between  md:h-100 md:p-8">
  @csrf
   @if ($errors->any())
      <div class="w-full p-3 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
          <ul class="list-disc pl-5">
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
  @endif

  <div class="p-2 flex flex-col flex-wrap justify-center items-start w-full">
      <label for="email" class="font-500">Email</label>
      <input type="email" placeholder="Enter Your Email" name="email" value="{{ old('email') }}" class="w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
  </div>
  
  <div class="p-2 flex flex-col flex-wrap items-start w-full">
      <label for="password" class="font-500">Password</label>
      <input type="password" name="password" class="w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]" placeholder="Enter Your Password">
  </div>
  
  <div class="text-xs flex justify-between flex-row flex-wrap items-center w-full p-2">
      <div class="">
          <input type="checkbox" name="remember">
          <label for="remember">Remember me</label>
      </div>
      <a href="" class="text-red-500 font-semibold">Forget your Password?</a>
  </div>
  
  <button type="submit" class="border-none bg-[#065e21] text-white w-full py-2 rounded-md my-4">Login</button>
  <a href="{{ route('register.show') }}" class="text-center w-full">Don't have an account? <span class="underline text-red-600 font-bold">Sign up</span></a>
</form>

@endsection()