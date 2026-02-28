@extends("web.app")
@section('content')
<div class="container mx-auto h-full flex-col gap-10  flex items-start justify-center px-4 md:w-[20rem] md:border md:border-green-800 md:rounded md:h-[30rem]">
    <h1>Please enter Your Email <br> To Reset Password</h1>
    <form action="{{ route('otp-forget-password.submit') }}" method="POST" class=" flex flex-col flex-wrap justify-center items-start w-full">
        @csrf
        <label for="">
            Enter your Email
        </label>
        <input type="text" name="email" placeholder="test@email.com" class=" w-full border rounded-md p-2 mt-2 outline-none border-[#cfcfcf]">
         <button class="border-none bg-[#065e21] text-white w-full py-2 rounded-md my-4" type="submit">confirm</button>
    </form>
</div>
@endsection

<!--  -->