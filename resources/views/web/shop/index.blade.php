@extends('web.app')
@section('page-title')
{{ $category->name }}
@endsection
@section('content')
  <div class="mt-32 ">
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 mt-4 sm:grid-cols-1 gap-12  px-[9%]">
        @foreach($products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
  </div>
  
@endsection