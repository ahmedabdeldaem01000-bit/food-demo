@extends('web.app')

@section('title', 'Your cart | Foodify')

@section('content')
    <div class="page-container">
        <div class="mb-7">
            <p class="eyebrow">Ready when you are</p>
            <h1 class="section-heading mt-2">Your cart</h1>
        </div>

        @if($cart->isEmpty())
            <div class="surface flex flex-col items-center px-6 py-14 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#edf4e9] text-2xl text-[#245a38]" aria-hidden="true">＋</div>
                <h2 class="mt-5 text-xl font-bold text-slate-900">Your cart is empty</h2>
                <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">Take a look at the menu and add something you’d like to enjoy.</p>
                <a href="{{ route('category.index') }}" class="btn-primary mt-6">Browse the menu</a>
            </div>
        @else
            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_21rem]">
                <div class="space-y-3">
                    @foreach($cart as $item)
                        <article class="surface flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                            <a href="{{ route('product.show', $item->product) }}" class="flex min-w-0 flex-1 items-center gap-4">
                                <img
                                    src="{{ $item->product->image ? asset('storage/' . $item->product->image) : asset('images/image1.jpg') }}"
                                    alt="{{ $item->product->name }}"
                                    class="h-20 w-20 shrink-0 rounded-xl object-cover sm:h-24 sm:w-24"
                                    loading="lazy"
                                >
                                <span class="min-w-0">
                                    <span class="block truncate font-bold text-slate-900">{{ $item->product->name }}</span>
                                    <span class="mt-1 block text-sm text-slate-500">{{ number_format((float) $item->product->price, 2) }} EGP each</span>
                                    <span class="mt-2 block font-semibold text-[#245a38]">{{ number_format((float) ($item->product->price * $item->quantity), 2) }} EGP</span>
                                </span>
                            </a>

                            <div class="flex items-center justify-between gap-4 sm:justify-end">
                                <div class="inline-flex items-center gap-2 rounded-xl border border-[#e7ebe5] p-1">
                                    <form action="{{ route('cart.decrease', $item->product->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" aria-label="Decrease {{ $item->product->name }} quantity" class="h-9 w-9 rounded-lg font-semibold text-slate-700 hover:bg-[#f2f5f0]">−</button>
                                    </form>
                                    <span class="min-w-6 text-center text-sm font-bold" aria-label="Quantity">{{ $item->quantity }}</span>
                                    <form action="{{ route('cart.increase', $item->product->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" aria-label="Increase {{ $item->product->name }} quantity" class="h-9 w-9 rounded-lg font-semibold text-slate-700 hover:bg-[#f2f5f0]">+</button>
                                    </form>
                                </div>
                                <form action="{{ route('cart.remove', $item->product->id) }}" method="POST" onsubmit="return confirm('Remove this item from your cart?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="Remove {{ $item->product->name }} from cart" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#a33f34] hover:bg-red-50">Remove</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="surface p-5 sm:p-6" aria-labelledby="order-summary">
                    <h2 id="order-summary" class="text-lg font-bold text-slate-900">Order summary</h2>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4 text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900">{{ number_format((float) $total, 2) }} EGP</span>
                        </div>
                        @if($discount)
                            <div class="flex justify-between gap-4 text-emerald-800">
                                <span>Discount ({{ $discount->code }})</span>
                                <span class="font-semibold">−{{ number_format((float) $discountAmount, 2) }} EGP</span>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4 text-slate-600">
                            <span>Delivery</span>
                            <span class="font-semibold text-slate-900">30.00 EGP</span>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-[#e7ebe5] pt-4 text-base font-bold text-slate-900">
                            <span>Total</span>
                            <span>{{ number_format((float) ($discountedSubtotal + 30), 2) }} EGP</span>
                        </div>
                    </div>
                    @if($discount)
                        <form action="{{ route('discount.remove') }}" method="POST" class="mt-4">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-[#a33f34] hover:underline">Remove discount</button>
                        </form>
                    @else
                        <form action="{{ route('discount.apply') }}" method="POST" class="mt-5 space-y-2">
                            @csrf
                            <label for="discount_code" class="field-label">Discount code</label>
                            <div class="flex gap-2">
                                <input id="discount_code" name="discount_code" type="text" maxlength="64" value="{{ old('discount_code', request()->query('discount_code')) }}" autocomplete="off" class="min-w-0 flex-1 px-3 py-2.5 uppercase" placeholder="Enter code">
                                <button type="submit" class="btn-secondary min-h-10 px-3 py-2">Apply</button>
                            </div>
                            @error('discount_code')
                                <p class="text-sm text-red-700" role="alert">{{ $message }}</p>
                            @enderror
                        </form>
                    @endif
                    <form action="{{ route('paypal.createPayment') }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" class="btn-primary w-full">Continue to PayPal</button>
                    </form>
                    <a href="{{ route('category.index') }}" class="mt-4 block text-center text-sm font-semibold text-[#245a38] hover:underline">Continue browsing</a>
                </aside>
            </div>
        @endif
    </div>
@endsection
