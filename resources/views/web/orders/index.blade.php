@extends('web.app')

@section('title', 'My orders | Foodify')

@section('content')
    <div class="page-container">
        <div class="mb-8">
            <p class="eyebrow">Order history</p>
            <h1 class="section-heading mt-2">My orders</h1>
            <p class="section-copy">Review your recent orders and their current status.</p>
        </div>

        @if($orders->isEmpty())
            <div class="surface flex flex-col items-center px-6 py-14 text-center">
                <span class="text-4xl text-[#245a38]" aria-hidden="true">▤</span>
                <h2 class="mt-4 text-xl font-bold text-slate-900">No orders yet</h2>
                <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">When you place an order, it will appear here.</p>
                <a href="{{ route('category.index') }}" class="btn-primary mt-6">Browse the menu</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    <section class="surface overflow-hidden" aria-labelledby="order-{{ $order->id }}">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e7ebe5] bg-[#fbfcfa] px-5 py-4 sm:px-6">
                            <div>
                                <h2 id="order-{{ $order->id }}" class="font-bold text-slate-900">Order #{{ $order->id }}</h2>
                                <p class="mt-1 text-sm text-slate-500">Placed {{ $order->created_at->format('M j, Y') }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-[#edf4e9] px-3 py-1.5 text-xs font-bold capitalize text-[#245a38]">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>

                        <div class="divide-y divide-[#f0f2ee] px-5 sm:px-6">
                            @foreach($order->items as $item)
                                <div class="flex items-center gap-4 py-4">
                                    @if($item->product)
                                        <a href="{{ route('product.show', $item->product) }}" class="shrink-0">
                                            <img
                                                src="{{ $item->product->image ? asset('storage/' . $item->product->image) : asset('images/image1.jpg') }}"
                                                alt="{{ $item->product->name }}"
                                                class="h-16 w-16 rounded-xl object-cover"
                                                loading="lazy"
                                            >
                                        </a>
                                    @else
                                        <div class="h-16 w-16 shrink-0 rounded-xl bg-[#f2f5f0]" aria-hidden="true"></div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-slate-900">{{ $item->product?->name ?? 'Item unavailable' }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Quantity: {{ $item->quantity }}</p>
                                    </div>
                                    @if($item->product)
                                        <form action="{{ route('cart.add', $item->product->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-secondary min-h-9 px-3 py-1.5 text-xs">Add again</button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-between gap-4 border-t border-[#e7ebe5] px-5 py-4 text-sm sm:px-6">
                            <span class="font-medium text-slate-500">Order total</span>
                            <span class="font-bold text-slate-900">{{ number_format((float) $order->amount, 2) }} {{ strtoupper($order->currency) }}</span>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
@endsection
