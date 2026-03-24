@extends('web.app')

@section('content')
    <div class="container mx-auto py-10 my-10">

        <div class="swiper mt-20 mySwiper p-4 w-[90%] ">
            <div class="swiper-wrapper items-center w-full gap-2.5  ">
<div class="swiper-slide grid md:grid-cols-2  items-center gap-6">

<div class="w-full md:flex">
    <div class="order-1 md:order-2  w-full  aspect-video  md:aspect-22/6  bg-contain bg-no-repeat  bg-center " style="border-radius: 100%; background-image: url('{{ asset('images/image1.jpg') }}');"></div>
        
            <div class="order-2 md:order-1 p-4 md:p-8 text-center md:text-left">
                 <h1 class="text-xl md:text-3xl font-bold">Lorem, ipsum dolor.</h1>
                        <p class="mt-3 text-sm md:text-base">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                        </p>
                        <button class="mt-4 px-5 py-2 bg-black text-white rounded-lg">
                            Shop now
                        </button>
            </div>
        </div>
</div>
<div class="swiper-slide grid md:grid-cols-2  items-center gap-6">

<div class="w-full md:flex">
    <div class="order-1 md:order-2  w-full  aspect-video  md:aspect-22/6  bg-contain bg-no-repeat  bg-center " style="border-radius: 100%; background-image: url('{{ asset('images/image2.jpg') }}');"></div>
        
            <div class="order-2 md:order-1 p-4 md:p-8 text-center md:text-left">
                 <h1 class="text-xl md:text-3xl font-bold">Lorem, ipsum dolor.</h1>
                        <p class="mt-3 text-sm md:text-base">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                        </p>
                        <button class="mt-4 px-5 py-2 bg-black text-white rounded-lg">
                            Shop now
                        </button>
            </div>
        </div>
</div>
<div class="swiper-slide grid md:grid-cols-2  items-center gap-6">

<div class="w-full md:flex">
    <div class="order-1 md:order-2  w-full  aspect-video  md:aspect-22/6  bg-contain bg-no-repeat  bg-center " style="border-radius: 100%; background-image: url('{{ asset('images/image3.jpg') }}');"></div>
        
            <div class="order-2 md:order-1 p-4 md:p-8 text-center md:text-left">
                 <h1 class="text-xl md:text-3xl font-bold">Lorem, ipsum dolor.</h1>
                        <p class="mt-3 text-sm md:text-base">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                        </p>
                        <button class="mt-4 px-5 py-2 bg-black text-white rounded-lg">
                            Shop now
                        </button>
            </div>
        </div>
</div>


  
            </div>
        </div>

   
    <div class="my-10  flex flex-col flex-wrap items-center justify-between gap-10 content-center container">
 
    <h1  class="p-4 mb-4 w-40  bg-white rounded justify-center text-center">Recommendations</h1>


    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 sm:grid-cols-1 gap-12 px-[5%]">
        @foreach($data['latest'] as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</div>
      <div class="my-10  flex flex-col flex-wrap items-center justify-between gap-10 content-center container">
    
    <h1  class="p-4 mb-4 w-40  bg-white rounded justify-center text-center">Best Selling</h1>


    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 sm:grid-cols-1 gap-12  px-[5%]">
        @foreach($data['best_selling'] as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</div>
<div class="flex flex-col items-center justify-center content-center w-full ">
    <h1 class="p-4 mb-4 w-40  bg-white rounded justify-center text-center">Categories</h1>
    <div class="category-slider my-6 w-[80%] h-40 justify-center ">
    <div class="swiper">
        <div class="swiper-wrapper">
            @foreach($data['sectionsWithProducts'] as $category)
                <div class="swiper-slide flex flex-col items-center p-2    w-40 h-40 bg-white rounded-full">
                    <a href="{{ route('category.show', $category->id) }}" class="flex flex-col items-center">
                        <img src="{{ asset('storage/'.$category->image) }}" 
                             alt="{{ $category->name }}" 
                             class="w-24 h-24 object-cover rounded-full mb-2">
                        <span class="font-semibold text-center">{{ $category->name }}</span>
                    </a>
                </div>
            @endforeach
        </div>

 

        <!-- Pagination -->
        <!-- <div class="swiper-pagination mt-16"></div> -->
    </div>
</div>
</div>
    </div>


         @push('script')
         @push('script')
<script>
    // Hero slider
    const heroSwiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: {
            delay: 3000,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

    // Categories slider 
    const categorySwiper = new Swiper('.category-slider .swiper', {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: true,
        pagination: {
            el: '.category-slider .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: { slidesPerView: 2, spaceBetween: 15 },
            768: { slidesPerView: 3, spaceBetween: 20 },
            1024: { slidesPerView: 4, spaceBetween: 25 },
            1280: { slidesPerView: 6, spaceBetween: 30 },
        }
    });
</script>
@endpush
       
@endsection

 