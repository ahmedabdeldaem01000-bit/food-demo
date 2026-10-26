@extends('web.app')

@section('title', $data->name . ' | Foodify')

@section('content')
    @php
        $imagePath = ltrim(str_replace('\\', '/', (string) $data->image), '/');

        while (str_starts_with($imagePath, 'storage/') || str_starts_with($imagePath, 'public/')) {
            $imagePath = substr($imagePath, strpos($imagePath, '/') + 1);
        }

        $imageUrl = $imagePath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath)
            : asset('images/products/image1.jpg');

        $ingredients = $data->ingredients;

        if (is_string($ingredients)) {
            $decodedIngredients = json_decode($ingredients, true);
            $ingredients = is_array($decodedIngredients)
                ? $decodedIngredients
                : explode(',', $ingredients);
        }

        if ($ingredients instanceof \Illuminate\Support\Collection) {
            $ingredients = $ingredients->all();
        }

        $ingredients = collect(is_array($ingredients) ? $ingredients : [])
            ->map(function ($ingredient, $key) {
                if (is_array($ingredient)) {
                    $name = $ingredient['name'] ?? $ingredient['ingredient'] ?? null;
                    $property = $ingredient['property'] ?? null;

                    if ($name !== null) {
                        return trim((string) $name . ($property ? ' - ' . (string) $property : ''));
                    }

                    $values = array_filter(
                        $ingredient,
                        fn ($value) => is_scalar($value) && trim((string) $value) !== ''
                    );

                    return implode(', ', array_map('strval', $values));
                }

                if (! is_scalar($ingredient)) {
                    return null;
                }

                $value = trim((string) $ingredient);

                return is_string($key) && $value !== ''
                    ? $key . ': ' . $value
                    : $value;
            })
            ->filter(fn ($ingredient) => is_string($ingredient) && $ingredient !== '')
            ->values();
    @endphp

    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <nav aria-label="Breadcrumb" class="mb-5">
            <a
                href="{{ $data->category ? route('category.show', $data->category->id) : route('category.index') }}"
                class="inline-flex min-h-9 items-center gap-2 rounded-md text-sm font-medium text-stone-500 transition hover:text-[#35523b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#35523b]"
            >
                <span aria-hidden="true">←</span>
                Back to menu
            </a>
        </nav>

        <article class="grid overflow-hidden rounded-2xl border border-[#e8e5dc] bg-white md:grid-cols-2">
            <div class="relative aspect-[4/3] overflow-hidden bg-[#f0eee8] sm:aspect-[16/10] md:aspect-auto md:min-h-[400px]">
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $data->name }}"
                    class="absolute inset-0 h-full w-full object-cover"
                    fetchpriority="high"
                >
            </div>

            <div class="flex flex-col p-5 sm:p-7 lg:p-9">
                @if ($data->category)
                    <a
                        href="{{ route('category.show', $data->category->id) }}"
                        class="w-fit text-xs font-semibold uppercase tracking-[0.12em] text-[#71816b] transition hover:text-[#35523b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#35523b]"
                    >
                        {{ $data->category->name }}
                    </a>
                @endif

                <h1 class="mt-2 text-2xl font-semibold leading-tight tracking-tight text-[#292c25] sm:text-3xl">
                    {{ $data->name }}
                </h1>

                <p class="mt-4 flex items-baseline gap-2 text-[#35523b]">
                    <span class="text-2xl font-semibold tracking-tight">{{ number_format((float) $data->price, 2) }}</span>
                    <span class="text-sm font-medium text-stone-500">EGP</span>
                </p>

                @if (filled($data->description))
                    <section class="mt-5">
                        <h2 class="text-sm font-semibold text-stone-800">About this dish</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $data->description }}</p>
                    </section>
                @endif

                @if (filled($data->kcal) || (is_numeric($data->review) && (float) $data->review > 0))
                    <dl class="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-t border-[#eeece5] pt-4 text-sm">
                        @if (filled($data->kcal))
                            <div class="flex items-baseline gap-2">
                                <dt class="text-stone-500">Calories</dt>
                                <dd class="font-medium text-stone-800">{{ $data->kcal }} kcal</dd>
                            </div>
                        @endif
                        @if (is_numeric($data->review) && (float) $data->review > 0)
                            <div class="flex items-baseline gap-2">
                                <dt class="text-stone-500">Rating</dt>
                                <dd class="font-medium text-stone-800">{{ number_format((float) $data->review, 1) }} / 5</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                @if ($ingredients->isNotEmpty())
                    <section class="mt-5 border-t border-[#eeece5] pt-4">
                        <h2 class="text-sm font-semibold text-stone-800">Ingredients</h2>
                        <ul class="mt-2 space-y-1 text-sm leading-6 text-stone-600">
                            @foreach ($ingredients as $ingredient)
                                <li>{{ $ingredient }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <div class="mt-auto pt-7">
                    @auth
                        <form action="{{ route('cart.add', $data->id) }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="min-h-11 w-full rounded-lg bg-[#35523b] px-4 py-3 text-sm font-medium text-white transition hover:bg-[#293f2e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#35523b]"
                            >
                                Add to cart
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ route('login.show') }}"
                            class="flex min-h-11 w-full items-center justify-center rounded-lg bg-[#35523b] px-4 py-3 text-sm font-medium text-white transition hover:bg-[#293f2e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#35523b]"
                        >
                            Log in to order
                        </a>
                    @endauth
                </div>
            </div>
        </article>
    </div>
@endsection
