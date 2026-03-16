<div
    class="bg-[#FBF6F6] relative rounded-lg p-2 shadow-md flex flex-wrap flex-row items-center justify-center content-center">
<a href="{{ route('product.show',$product->id) }}">
        <div class="flex flex-wrap relative flex-row items-center w-full justify-between content-center">
        <!-- image + title -->
        <div class="w-[50%]"> <!-- title +description -->
            <h3 class="mt-3 text-lg text-start font-semibold">
                {{ $product->name }}
            </h3>
            <p class="text-xs">{{ $product->description }}</p>
        </div>
        <div class="w-28  md:w-[40%] relative -top-12 left-8 "> <!-- image -->
            <!-- <img src="{{ asset("storage/" . $product->image) }}" class="w-full    object-cover rounded-full"> -->
         
            <img src="{{ asset("storage/" . $product->image) }}" class="w-full    object-cover rounded-full">

        </div>

    </div>
</a>

    <div class="flex flex-wrap flex-row items-center justify-between content-center w-full">

        <div class="flex flex-wrap flex-col items-center justify-center content-center">
            <div class="mt-2 font-semibold text-sm">
                <p>{{ $product->price }} <span>EGP</span></p>
            </div>
            <div class="product-rating mt-2 flex items-center">
                @php
                    $review = $product->review ?? 0; // لو مفيش review يبقى 0
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
          
                <p> {{ $product->name ?? '' }}</p>
  
            </span>
            <span class="flex gap-4 flex-row flex-wrap items-center justify-center content-center">
                <span class=" flex flex-row flex-wrap justify-center rounded items-center gap-2 bg-[#FFFFFF] w-8 h-8">
               
  <form action="{{ route('favorites.toggle', $product->id) }}" method="POST">
    @csrf
    <button type="submit" class="btn {{ $product->favorited_by_exists ? 'btn-danger' : 'btn-outline-danger' }}">
    @if($product->is_favorite)
      
            <i class="fa-solid fa-heart text-red-600"></i>
        @else
          
            <i class="fa-regular fa-heart text-gray-400"></i>
        @endif
    </button>
</form>
                </span>
             
                <form action="{{ route('cart.add',$product->id) }}" method="POST">
@csrf
   <button class="border-none bg-green-800 rounded-xl text-white px-6 mt-2 py-2"> Add </button>
</form>

            </span>

        </div>
    </div>
</div>