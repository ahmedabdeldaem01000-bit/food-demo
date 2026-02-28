@extends("web.app")

@section('content')
 
<div class="container mx-auto h-screen flex-col gap-10   flex items-center justify-center p-4 md:w-[20rem] md:border md:border-green-800 rounded md:h-120">
<div class="gap-10 flex flex-col">
    @if ($errors->any())
        <div class="w-full p-3 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
        <p class="text-[13px] text-start font-semibold justify-start content-start">Create a new password Ensure it differs from<br>previous ones for security</p>
    <form action="{{ route('update-new-password.submit') }}"  method="POST" class=" flex flex-col  flex-wrap  items-center justify-center  ">
    @csrf
     <div class="flex  gap-10 flex-wrap content-start w-full flex-col items-start">
         <div class="w-full"> <label for="">
                Password
            </label>
            <input type="password" placeholder="Enter Your Password" name="password"
                class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
        </div>
        <div class="w-full"> 
            <label for="">
                Confirm Password
            </label>
            <input type="password" placeholder="Re-enter Your password" name="password_confirmation"
                class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
        </div>
        <button class="border-none bg-[#065e21] text-white w-full py-2 rounded-md my-4" type="submit">Continue</button>
       </div>
    </form>
</div>
</div>
@endsection