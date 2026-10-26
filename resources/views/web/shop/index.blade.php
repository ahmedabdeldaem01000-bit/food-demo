@extends('web.app')

@section('title', $category->name . ' | Foodify')

@section('content')
    <div class="page-container">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('category.index') }}" class="text-sm font-semibold text-[#245a38] hover:underline">← All categories</a>
                <p class="eyebrow mt-5">Browse category</p>
                <h1 class="section-heading mt-2">{{ $category->name }}</h1>
                <p class="section-copy">Dishes available in this category.</p>
            </div>
        </div>

        @if($products->isNotEmpty())
            <div class="product-grid">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="surface px-6 py-12 text-center">
                <h2 class="font-semibold text-slate-800">No dishes in this category yet.</h2>
                <p class="mt-2 text-sm text-slate-500">Try another category from the menu.</p>
                <a href="{{ route('category.index') }}" class="btn-primary mt-5">Browse categories</a>
            </div>
        @endif
    </div>
@endsection
