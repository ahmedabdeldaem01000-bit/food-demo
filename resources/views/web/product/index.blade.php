@extends('web.app')
@section('content')

<div class="flex flex-wrap flex-row w-full  min-h-[100vh]  justify-center items-center content-center">
       
    <div
        class="mb-8  md:w-[50%] w-[80%] p-4 px-[5%] relative flex bg-white shadow-md flex-wrap flex-row items-center justify-center content-center">
        <div class="w-40 relative  ">
            <img src={{asset('storage/' . $data->image)}} class="rounded-full left-16 -top-15 absolute  w-full" alt="">
        </div>
        <div class="flex flex-wrap w-full flex-col items-start justify-center gap-4">
            <h1 class="mb-2 font-bold"> {{ $data->name }}</h1>
            <h1 class="mb-2 font-bold"> {{ $data->category->name }}</h1>
            <h1 class="mb-2 text-[#666A67] text-sm"> {{ $data->description }}</h1>
            <h1 class="mb-2 font-bold text-black "> 
               @foreach ($data->ingredients as $ingredient)
        <span>{{ $ingredient['name'] }}</span>
    @endforeach
</h1>
            <span class="flex flex-wrap  w-full flex-row justify-between items-center content-center ">
                <h1 class="mb-2 font-semibold"> ingredients </h1>
                <span class="flex flex-row flex-wrap justify-baseline gap-4 items-center"> <i class=" text-green-800 fa-solid fa-fire-flame-curved"></i>
                    <h1>kcal {{ $data->kcal}}</h1>
                </span>
            </span>
        </div>
 <button class="border-none bg-green-800 rounded-xl text-white px-6 mt-2 py-2"> Add To Cart</button>
    </div>
</div>
@endsection