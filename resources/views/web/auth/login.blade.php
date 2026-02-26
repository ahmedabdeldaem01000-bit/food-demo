@extends('web.app')
@section('content')

<form action="" class="p-4 flex flex-col flex-wrap content-center justify-center w-full items-start md:w-100 md:border md:border-green-800 md:rounded-[15px] md:justify-between  md:h-[25rem] md:p-8">
 
  <div class="p-2 flex flex-col flex-wrap justify-center items-start w-full">
 
      <label for="" class="font-500">Phone Number</label>
    <input type="text" placeholder="Enter Your phone Number" class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
  </div>
  <div class="p-2 flex flex-col flex-wrap items-start w-full">
      <label for="" class="font-500">Password</label>
    <input type="text" class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]" placeholder="Enter Your Password">
  </div>
 
<div class="text-xs flex justify-between flex-row flex-wrap items-center w-full p-2">
      <div class="">
      <input type="checkbox">
      <label for="">Remember my</label>
  </div>
  <a href="" class="text-red-500 font-semibold">Forget your Password?</a>
</div>
  <button class="border-none bg-[#065e21] text-white w-full py-2 rounded-md my-4">Login</button>
  <a href="" class="text-center w-full ">Don't have an account? <span class="underline text-red-600 font-bold">Sign up</span></a>
</form>
 
@endsection()
 