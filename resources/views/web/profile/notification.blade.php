@extends('web.app')
@section('page-title')
    Notification
@endsection
@section('content')
<div class="max-w-md mx-auto bg-gray-100 min-h-screen p-5 mt-32">

    <!-- Today -->
    <h2 class="text-lg font-semibold mb-4">Today</h2>

    <div class="space-y-5">

        <div class="flex items-start gap-3">
            <img src="{{ asset('images/image1.jpg') }}" 
                 class="w-12 h-12 rounded-full object-cover">

            <p class="text-gray-700 text-sm leading-relaxed">
                Your meal is on the way! 🚚 Expected delivery: 20 minutes.
            </p>
        </div>

        <div class="flex items-start gap-3">
            <img src="{{ asset('images/image2.jpg') }}" 
                 class="w-12 h-12 rounded-full object-cover">

            <p class="text-gray-700 text-sm leading-relaxed">
                Order received ✅ Thanks for choosing Foodify ❤️
            </p>
        </div>

    </div>

    <!-- Last Week -->
    <h2 class="text-lg font-semibold mt-10 mb-4">Last Week</h2>

    <div class="space-y-5">

        <div class="flex items-start gap-3">
            <img src="{{ asset('images/image3.jpg') }}" 
                 class="w-12 h-12 rounded-full object-cover">

            <p class="text-gray-700 text-sm leading-relaxed">
                Did you know? Avocados are a great source of healthy fats 🥑
            </p>
        </div>

        <div class="flex items-start gap-3">
            <img src="{{ asset('images/image4.jpg') }}" 
                 class="w-12 h-12 rounded-full object-cover">

            <p class="text-gray-700 text-sm leading-relaxed">
                Swap idea: Replace sugary drinks with fresh juice 🍊
            </p>
        </div>

        <div class="flex items-start gap-3">
            <img src="{{ asset('images/image5.jpg') }}" 
                 class="w-12 h-12 rounded-full object-cover">

            <p class="text-gray-700 text-sm leading-relaxed">
                Tip of the day: Add more colorful veggies — more colors = more nutrients 🌈
            </p>
        </div>

    </div>

</div>
@endsection