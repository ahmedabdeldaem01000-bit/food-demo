@extends('web.app')
@section('page-title')
    my orders
@endsection
@section('content')
    <div class="mt-32 flex flex-wrap flex-row items-center justify-center content-center ">
 <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 items-center mt-4 sm:grid-cols-1 gap-12 h-[100%] px-[9%]">
            @foreach($orders as $order)
        
            @foreach ($order->items as $item)
                     <div
                        class="bg-[#FBF6F6] relative  rounded-lg p-2 shadow-md flex flex-wrap flex-row items-center justify-center content-center">
                        <a href="{{ route('product.show', $item->product) }}">
                            <div class="flex flex-wrap relative flex-row items-center w-full justify-between content-center">
                                <!-- image + title -->
                                <div class="w-[50%]"> <!-- title +description -->
                                    <h3 class="mt-3 text-lg text-start font-semibold">
                                        {{ $item->product->name }}
                                    </h3>
                                    <p class="text-xs">{{ $item->product->description }}</p>
                                </div>
                                <div class="w-28  md:w-[40%] relative -top-12 left-8 "> <!-- image -->
                                    <!-- <img src="{{ asset("storage/" . $order->image) }}" class="w-full    object-cover rounded-full"> -->

                                    <img src="{{ asset("storage/" . $item->product->image) }}"
                                        class="w-full    object-cover rounded-full">

                                </div>

                            </div>
                        </a>

                        <div class="flex flex-wrap flex-row items-center justify-between content-center w-full">

                            <div class="flex flex-wrap flex-col items-center justify-center content-center">
                                <div class="mt-2 font-semibold text-sm">
                                    <p>{{ $item->product->price }} <span>EGP</span></p>
                                </div>
                                <div class="order-rating mt-2 flex items-center">
                                    @php
                                        $review = $item->product->review ?? 0; // لو مفيش review يبقى 0
                                    @endphp

                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review)
                                            <span class="text-yellow-500 text-lg">★</span>
                                        @else
                                            <span class="text-gray-300 text-lg">★</span>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                            <div class="flex flex-wrap flex-col items-center justify-center content-center">
                                <span class="flex flex-row flex-wrap items-center gap-2 ">

                                    <i class=" text-green-800 fa-solid fa-fire-flame-curved"></i>

                                    <p> {{ $item->product->name ?? '' }}</p>

                                </span>
                                <span class="flex gap-4 flex-row flex-wrap items-center justify-center content-center">
                                    <span
                                        class=" flex flex-row flex-wrap justify-center rounded items-center gap-2 bg-[#FFFFFF] w-8 h-8">

                                        <form action="{{ route('favorites.toggle', $item->product->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="btn {{ $item->product->favorited_by_exists ? 'btn-danger' : 'btn-outline-danger' }}">
                                                @if($item->product->is_favorite)

                                                    <i class="fa-solid fa-heart text-red-600"></i>
                                                @else

                                                    <i class="fa-regular fa-heart text-gray-400"></i>
                                                @endif
                                            </button>
                                        </form>
                                    </span>

                                    <form action="{{ route('cart.add', $item->product->id) }}" method="POST">
                                        @csrf
                                        <button class="border-none bg-green-800 rounded-xl text-white px-6 mt-2 py-2"> Add </button>
                                    </form>

                                </span>

                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach

        </div>
         
    </div>
@endsection