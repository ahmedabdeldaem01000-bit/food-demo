@extends('web.app')

@section('title', 'Menu categories | Foodify')

@section('content')
    <div class="page-container">
        <div class="mb-8">
            <p class="eyebrow">Explore the menu</p>
            <h1 class="section-heading mt-2">Menu categories</h1>
            <p class="section-copy">Choose a category to see the dishes available.</p>
        </div>

        @if($categories->isNotEmpty())
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($categories as $category)
                    <a href="{{ route('category.show', $category->id) }}" class="group overflow-hidden rounded-2xl border border-[#e7ebe5] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <img
                            src="{{ $category->image ? asset('storage/' . $category->image) : asset('images/image1.jpg') }}"
                            alt=""
                            class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                            loading="lazy"
                        >
                        <div class="flex items-center justify-between gap-2 px-4 py-3">
                            <span class="font-semibold text-slate-800 group-hover:text-[#245a38]">{{ $category->name }}</span>
                            <span class="text-[#245a38]" aria-hidden="true">→</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="surface px-6 py-12 text-center">
                <h2 class="font-semibold text-slate-800">No categories are available yet.</h2>
                <p class="mt-2 text-sm text-slate-500">Please check back soon.</p>
            </div>
        @endif
    </div>
@endsection
