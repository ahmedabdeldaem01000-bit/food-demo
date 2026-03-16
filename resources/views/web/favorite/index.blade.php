@extends('web.app')
@section('content')
<!-- display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    align-content: center;
    justify-content: space-evenly;
    align-items: center; -->
    @section('page-title')
    favorite
    @endsection
<div class="container w-full flex min-h-screen flex-wrap flex-col justify-evenly items-center content-center gap-4">
    <div class="w-full  flex items-center justify-center">
    
</div>
 @if (empty($favorites->is_fav))
 <div class="w-full justify-center flex flex-wrap flex-row items-center content-center">
        <img src="{{ asset('images/favorite.png') }}"/>
    </div>
  
 @endif
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 sm:grid-cols-1 gap-12  px-[5%]">
        @foreach($favorites as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
    
</div>
@endsection