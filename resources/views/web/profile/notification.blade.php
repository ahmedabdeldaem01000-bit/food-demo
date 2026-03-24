@extends('web.app')
@section('page-title')
    my orders
@endsection

@section('content')
<div class="p-4 flex flex-wrap items-center justify-center content-center w-full gap-4 mt-8 h-screen">
    @foreach($notifications as $notification)
<a class="flex items-start justify-center flex-wrap flex-col w-48 gap-4  bg-white shadow-md rounded-2xl p-4"  href="{{ route('product.show',$notification->product_id) }} ">
    <img src="{{ asset("storage/".$notification->image ?? 'images/default.jpg') }}" 
         class="w-12 h-12 rounded-full object-cover">
 
    <p class="text-black text-sm leading-relaxed">
        {{ $notification->body ?? 'New notification' }}
    </p>
    <p class="text-black text-sm leading-relaxed">
        {{ $notification->description ?? 'New notification' }}
    </p>
    <p class="text-black text-sm leading-relaxed">
        {{ $notification->created_at ?? 'New notification' }}
    </p>
</a>
@endforeach
</div>
@endsection