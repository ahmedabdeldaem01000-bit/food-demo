@extends("web.app")
@section('content')
<div class="container mx-auto h-full flex-col gap-10  flex items-start justify-center px-4 md:w-[20rem] md:border md:border-green-800 md:rounded md:h-[30rem]">
    <h1>Please enter Your Phone Number <br> To Reset Password</h1>
    <form action="" class=" flex flex-col flex-wrap justify-center items-start w-full">
        <label for="">
            Enter your Phone Number
        </label>
        <input type="text" placeholder="+20 1xxxxxxxx" class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
         <button class="border-none bg-[#065e21] text-white w-full py-2 rounded-md my-4">confirm</button>
    </form>
</div>
@endsection

<!--  -->