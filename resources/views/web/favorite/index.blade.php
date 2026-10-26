@extends('web.app')

@section('title', 'Favorites | Foodify')

@section('content')
    <div class="page-container">
        <div class="mb-8">
            <p class="eyebrow">Saved for later</p>
            <h1 class="section-heading mt-2">Your favorites</h1>
            <p class="section-copy">Your saved dishes, all in one place.</p>
        </div>

        @if($favorites->isEmpty())
            <div class="surface flex flex-col items-center px-6 py-14 text-center">
                <span class="text-4xl text-[#245a38]" aria-hidden="true">♡</span>
                <h2 class="mt-4 text-xl font-bold text-slate-900">No favorites yet</h2>
                <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">Save dishes from the menu and they’ll be easy to find here.</p>
                <a href="{{ route('category.index') }}" class="btn-primary mt-6">Explore the menu</a>
            </div>
        @else
            <div class="product-grid">
                @foreach($favorites as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
