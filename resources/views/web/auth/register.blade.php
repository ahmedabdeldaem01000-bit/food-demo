@extends('web.app')
@section('content')
<form action="{{ route('register.submit') }}" method="POST"
    class="p-4 flex flex-col flex-wrap  content-center justify-center w-full items-start md:w-100 md:border md:border-green-800 md:rounded-[15px] md:justify-between  md:h-full md:p-8">
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
        <label for="name" class="font-500">Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter Your Full Name"
            class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
    </div>

    <div class="p-2 flex flex-col flex-wrap justify-center items-start w-full">
        <label for="phone" class="font-500">Phone Number</label>
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Enter Your phone Number"
            class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
    </div>

    <div class="p-2 flex flex-col flex-wrap justify-center items-start w-full">
        <label for="email" class="font-500">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter Your Email"
            class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
    </div>

    <div class="p-2 flex flex-col flex-wrap items-start w-full">
        <label for="password" class="font-500">Password</label>
        <input type="password" name="password" class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]"
            placeholder="Enter Your Password">
    </div>

    <button type="submit" class="border-none bg-[#065e21] text-white w-full py-2 rounded-md my-4">Continue</button>
    <a href="{{ route('login.show') }}" class="text-center w-full block">Already have an account? <span class="underline text-red-600 font-bold">Sign in</span></a>
</form>
@endsection()