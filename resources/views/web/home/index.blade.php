@extends('web.app')

@section('title', 'Foodify | Fresh from our kitchen')

@section('content')
    @php($featuredProduct = $data['latest']->first())

    <div class="page-container space-y-14">
        <section class="grid overflow-hidden rounded-[1.75rem] bg-[#e9f0e5] md:min-h-[24rem] md:grid-cols-2" aria-labelledby="welcome-heading">
            <div class="flex flex-col items-start justify-center px-6 py-10 sm:px-10 md:px-12 md:py-14">
                <p class="eyebrow">Good food, made easy</p>
                <h1 id="welcome-heading" class="mt-3 max-w-xl text-4xl font-extrabold leading-tight tracking-[-0.045em] text-[#183b28] sm:text-5xl">
                    A fresh favorite is just a few clicks away.
                </h1>
                <p class="mt-4 max-w-lg text-base leading-7 text-[#536b5a]">
                    Explore the menu, find something you love, and order directly from Foodify.
                </p>
                <a href="{{ route('category.index') }}" class="btn-primary mt-7">Explore the menu <span aria-hidden="true">→</span></a>
            </div>
            <div class="relative min-h-64 bg-[#d9e6d3] md:min-h-full">
                @if($featuredProduct)
                    <img
                        src="{{ $featuredProduct->image ? asset('storage/' . $featuredProduct->image) : asset('images/image1.jpg') }}"
                        alt="{{ $featuredProduct->name }}"
                        class="absolute inset-0 h-full w-full object-cover"
                    >
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent px-6 pb-6 pt-16 text-white sm:px-8">
                        <p class="text-sm font-medium text-white/80">From the menu</p>
                        <h2 class="mt-1 text-xl font-bold">{{ $featuredProduct->name }}</h2>
                        <p class="mt-1 font-semibold">{{ number_format((float) $featuredProduct->price, 2) }} EGP</p>
                    </div>
                @else
                    <img src="{{ asset('images/image1.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                @endif
            </div>
        </section>

        @if($data['sectionsWithProducts']->isNotEmpty())
            <section aria-labelledby="categories-heading">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">Find your next favorite</p>
                        <h2 id="categories-heading" class="section-heading mt-2">Browse categories</h2>
                    </div>
                    <a href="{{ route('category.index') }}" class="hidden text-sm font-semibold text-[#245a38] hover:underline sm:inline">All categories <span aria-hidden="true">→</span></a>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($data['sectionsWithProducts'] as $category)
                        <a href="{{ route('category.show', $category->id) }}" class="group overflow-hidden rounded-2xl border border-[#e7ebe5] bg-white transition hover:-translate-y-0.5 hover:shadow-md">
                            <img src="{{ asset('storage/' . $category->image) }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy">
                            <span class="block px-3 py-3 text-sm font-semibold text-slate-800 group-hover:text-[#245a38]">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section aria-labelledby="latest-heading">
            <div class="mb-5">
                <p class="eyebrow">Recently added</p>
                <h2 id="latest-heading" class="section-heading mt-2">New on the menu</h2>
            </div>
            @if($data['latest']->isNotEmpty())
                <div class="product-grid">
                    @foreach($data['latest'] as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @else
                <div class="surface px-6 py-10 text-center">
                    <p class="font-semibold text-slate-800">The menu is being prepared.</p>
                    <p class="mt-2 text-sm text-slate-500">Please check back soon for available dishes.</p>
                </div>
            @endif
        </section>

        <section aria-labelledby="popular-heading">
            <div class="mb-5">
                <p class="eyebrow">More to explore</p>
                <h2 id="popular-heading" class="section-heading mt-2">More from the menu</h2>
            </div>
            @if($data['best_selling']->isNotEmpty())
                <div class="product-grid">
                    @foreach($data['best_selling'] as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @else
                <div class="surface px-6 py-10 text-center">
                    <p class="font-semibold text-slate-800">No dishes to show just yet.</p>
                    <p class="mt-2 text-sm text-slate-500">New menu items will appear here when available.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
