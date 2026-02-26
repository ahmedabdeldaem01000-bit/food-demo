@extends('web.app')

@section('content')
<div class="min-h-screen flex items-center justify-center w-screen px-4">

    <div class="w-screen md:w-96    bg-white rounded-2xl   ">

        <div class="text-center mb-6">
            <p class="text-xs font-semibold  text-gray-800">Please Enter the OTP to your phone number :</p>
            <p class="text-xs font-semibold  text-gray-800 mt-2">
                +20 1xxxxxxxx
            </p>
        </div>

        <form method="POST" >
            @csrf

            <div class="flex justify-between gap-2 mb-6">
                @for ($i = 0; $i < 4; $i++)
                    <input 
                        type="text"
                        name="otp[]"
                        maxlength="1"
                        placeholder="-"
                        class="w-12 h-12 bg-gray-200  text-center text-xl font-semibold border-none rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-green-600 transition"
                        required
                    />
                @endfor
            </div>

            <button 
                type="submit"
                class="w-full bg-green-800 hover:bg-gray-200 text-white font-semibold py-3 rounded-lg transition duration-300"
            >
                Verify Code
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-500">
                <a href="" class="text-green-700 font-semibold hover:underline">
             Did not get the code? 
                    
                </a>
                Resend the code in 59 secs
            </p>
            
        </div>

    </div>
</div>
@endsection