@extends('web.app')
@section('page-title')
Categories
@endsection
@section('content')
<div class="w-full flex flex-wrap flex-row items-center justify-center content-center">

<div class="w-full md:w-[65%] mt-32 px-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3   gap-4">
 
@foreach ($categories as $category)

<div class="shadow-md bg-white rounded-lg p-3 flex flex-col items-center justify-center hover:shadow-lg transition">

    <div class="w-full h-32 overflow-hidden rounded-md">
        <img 
            src="{{ asset('/storage/'.$category->image) }}" 
            alt="{{ $category->name }}"
            class="w-full h-full object-cover"
        >
    </div>

    <a class="mt-3 text-center bg-green-800 hover:bg-green-700 transition rounded-md px-4 py-2 text-white w-full text-sm" href="{{route('category.show',$category->id)  }}"> 
        {{ $category->name }}
    </a>

</div>

@endforeach

</div>
</div>

@endsection