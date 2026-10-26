<article class="product-card flex h-full flex-col">
    <a href="{{ route('product.show', $product->id) }}" class="block">
        <div class="relative">
            <img
                src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/image1.jpg') }}"
                alt="{{ $product->name }}"
                class="aspect-[4/3] w-full object-cover"
                loading="lazy"
            >
            @if($product->category)
                <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-[#245a38] shadow-sm">{{ $product->category->name }}</span>
            @endif
        </div>
        <div class="p-4 pb-2">
            <div class="flex items-start justify-between gap-3">
                <h3 class="font-bold leading-snug text-slate-900">{{ $product->name }}</h3>
                <span class="shrink-0 text-sm font-bold text-[#245a38]">{{ number_format((float) $product->price, 2) }} EGP</span>
            </div>
            @if($product->description)
                <p class="mt-2 line-clamp-2 text-sm leading-5 text-slate-500">{{ $product->description }}</p>
            @endif
            @if($product->kcal)
                <p class="mt-3 text-xs font-medium text-slate-500">{{ $product->kcal }} kcal</p>
            @endif
        </div>
    </a>

    <div class="mt-auto flex items-center justify-between gap-3 px-4 pb-4 pt-2">
        @auth
            <form action="{{ route('favorites.toggle', $product->id) }}" method="POST">
                @csrf
                <button
                    type="submit"
                    aria-label="{{ $product->is_favorite ? 'Remove ' . $product->name . ' from favorites' : 'Add ' . $product->name . ' to favorites' }}"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#e7ebe5] text-lg transition hover:bg-[#f6f7f4] {{ $product->is_favorite ? 'text-rose-600' : 'text-slate-500' }}"
                >{{ $product->is_favorite ? '♥' : '♡' }}</button>
            </form>
        @else
            <a href="{{ route('login.show') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#e7ebe5] text-lg text-slate-500 transition hover:bg-[#f6f7f4]" aria-label="Log in to save {{ $product->name }} to favorites">♡</a>
        @endauth

        @auth
            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary min-h-10 px-4 py-2">Add to cart</button>
            </form>
        @else
            <a href="{{ route('login.show') }}" class="btn-primary min-h-10 px-4 py-2">Log in to order</a>
        @endauth
    </div>
</article>
