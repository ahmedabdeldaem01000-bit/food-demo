@extends("web.app")

@section('content')

<div class="container mx-auto h-screen flex-col gap-10   flex items-center justify-center p-4 md:w-[20rem] md:border md:border-green-800 rounded md:h-[30rem]">
<div class="gap-10 flex flex-col">
    <!-- gap: 10px;
    display: flex;
    flex-direction: column; -->
        <p class="text-[13px] text-start font-semibold justify-start content-start">Create a new password Ensure it differs from<br>previous ones for security</p>
    <form action="" class=" flex flex-col  flex-wrap  items-center justify-center  ">

    <div class="flex  gap-10 flex-wrap content-start w-full flex-col items-start">
         <div class="w-full"> <label for="">
                Password
            </label>
            <input type="text" placeholder="Enter Your Password"
                class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
        </div>
        <div class="w-full"> 
            <label for="">
                Confirm Password
            </label>
            <input type="text" placeholder="Re-enter Your password"
                class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
        </div>
        <button class="border-none bg-[#065e21] text-white w-full py-2 rounded-md my-4">Continue</button>
       </div>
    </form>
</div>
</div>
@endsection