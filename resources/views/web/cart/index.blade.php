@extends('web.app')

@section('page-title')
Cart
@endsection

@section('content')

@if($cart->isEmpty())

<div class="w-full min-h-screen flex justify-center items-center">
    <img class="w-48 md:w-64" src="{{ asset('images/cart.png') }}" alt="Empty Cart">
</div>

@else

<div class="max-w-6xl mx-auto mt-32  px-4">

    <!-- cart items -->
    <div class="space-y-6"> 

        @foreach($cart as $item)
<div class="bg-[#FBF6F6] rounded-lg p-4 shadow-md flex items-center justify-between">

    <div class="flex items-center gap-4 w-1/2">

        <img src="{{ asset('storage/'.$item->product->image) }}"
             class="w-20 h-20 object-cover rounded-full">

        <div>
            <h3 class="text-lg font-semibold">
                {{ $item->product->name }}
            </h3>

            <p class="text-xs text-gray-500">
                {{ Str::limit($item->product->description,50) }}
            </p>

            <p class="mt-2 font-semibold text-green-700">
                {{ $item->product->price }} EGP
            </p>
        </div>

    </div>

    <!-- quantity -->

    <div class="flex items-center gap-3">

        <form action="{{ route('cart.decrease',$item->product->id) }}" method="POST">
            @csrf
            <button class="bg-gray-200 w-8 h-8 rounded">-</button>
        </form>

        <span class="font-semibold">
            {{ $item->quantity }}
        </span>

        <form action="{{ route('cart.increase',$item->product->id) }}" method="POST">
            @csrf
            <button class="bg-gray-200 w-8 h-8 rounded">+</button>
        </form>

    </div>

    <!-- total -->

    <div class="font-semibold text-lg text-green-800">

        {{ $item->product->price * $item->quantity }} EGP

    </div>

    <!-- remove -->

    <form action="{{ route('cart.remove',$item->product->id) }}" method="POST">
        @csrf
        @method('DELETE')

        <button class="text-red-500">
            <i class="fa-solid fa-trash"></i>
        </button>

    </form>

</div>

        @endforeach

    </div>

    <!-- summary -->

    <div class="mt-10 bg-gray-50 p-6 rounded-lg shadow-sm max-w-md ml-auto">

        <div class="flex justify-between mb-3">
            <span>Subtotal</span>
            <span>{{ $total }} EGP</span>
        </div>

        <div class="flex justify-between mb-3">
            <span>Delivery</span>
            <span>30 EGP</span>
        </div>

        <div class="flex justify-between text-lg font-bold border-t pt-3">
            <span>Total</span>
            <span>{{ $total + 30 }} EGP</span>
        </div>
       <form action="{{ route('paypal.createPayment') }}" method="POST">
    @csrf
    <button type="submit" class="bg-yellow-500 px-6 py-2 rounded text-white w-full">
        Pay All with PayPal
    </button>
</form>

    </div>

</div>

@endif

@endsection