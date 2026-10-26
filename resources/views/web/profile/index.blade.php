@extends('web.app')

@section('title', 'Account | Foodify')

@section('content')
    <div class="page-container max-w-4xl">
        <div class="mb-8">
            <p class="eyebrow">Your account</p>
            <h1 class="section-heading mt-2">Welcome, {{ auth()->user()->name }}</h1>
            <p class="section-copy">Manage your details and keep up with your orders.</p>
        </div>

        <div class="surface flex flex-col items-start gap-5 p-5 sm:flex-row sm:items-center sm:p-7">
            <img
                src="{{ auth()->user()->photo ? asset(auth()->user()->photo) : asset('images/default.png') }}"
                alt=""
                class="h-20 w-20 rounded-2xl object-cover ring-1 ring-[#e7ebe5]"
            >
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-bold text-slate-900">{{ auth()->user()->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ auth()->user()->email }}</p>
                @if(auth()->user()->phone)
                    <p class="mt-1 text-sm text-slate-500">{{ auth()->user()->phone }}</p>
                @endif
            </div>
            <a href="{{ route('profile.personalEditShow') }}" class="btn-secondary w-full sm:w-auto">Edit profile</a>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <a href="{{ route('profile.showOrders') }}" class="surface group flex items-center gap-4 p-5 transition hover:border-[#cbdcc9]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#edf4e9] font-bold text-[#245a38]" aria-hidden="true">01</span>
                <span class="flex-1">
                    <span class="block font-bold text-slate-900 group-hover:text-[#245a38]">My orders</span>
                    <span class="mt-1 block text-sm text-slate-500">See your order history and status</span>
                </span>
                <span class="text-slate-400" aria-hidden="true">→</span>
            </a>
            <a href="{{ route('get-cart.index') }}" class="surface group flex items-center gap-4 p-5 transition hover:border-[#cbdcc9]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#edf4e9] font-bold text-[#245a38]" aria-hidden="true">{{ $cartCount }}</span>
                <span class="flex-1">
                    <span class="block font-bold text-slate-900 group-hover:text-[#245a38]">Your cart</span>
                    <span class="mt-1 block text-sm text-slate-500">Continue with the items you selected</span>
                </span>
                <span class="text-slate-400" aria-hidden="true">→</span>
            </a>
        </div>

        <section id="my-coupons" class="mt-10 scroll-mt-24" aria-labelledby="my-coupons-heading">
            <div class="mb-5">
                <p class="eyebrow">Offers for you</p>
                <h2 id="my-coupons-heading" class="section-heading mt-2">My Coupons</h2>
                <p class="section-copy mt-2">Use an available code in your cart before checkout.</p>
            </div>

            @if($coupons->isEmpty())
                <div class="surface px-6 py-10 text-center">
                    <h3 class="font-semibold text-slate-900">No coupons available right now</h3>
                    <p class="mt-2 text-sm text-slate-500">Check back later for new offers.</p>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($coupons as $coupon)
                        <article class="surface flex h-full flex-col p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-bold text-slate-900">{{ $coupon->name }}</h3>
                                    @if($coupon->description)
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $coupon->description }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Available</span>
                            </div>

                            <p class="mt-4 text-xl font-extrabold text-[#245a38]">
                                @if($coupon->type === 'percentage')
                                    {{ rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') }}% off
                                @else
                                    {{ number_format((float) $coupon->value, 2) }} EGP off
                                @endif
                            </p>

                            <dl class="mt-4 space-y-2 border-t border-[#e7ebe5] pt-4 text-sm">
                                @if((float) $coupon->minimum_order_amount > 0)
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Minimum order</dt>
                                        <dd class="font-medium text-slate-800">{{ number_format((float) $coupon->minimum_order_amount, 2) }} EGP</dd>
                                    </div>
                                @endif
                                @if($coupon->ends_at)
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Valid until</dt>
                                        <dd class="font-medium text-slate-800">{{ $coupon->ends_at->format('M j, Y') }}</dd>
                                    </div>
                                @endif
                                @if($coupon->usage_limit !== null)
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Uses remaining</dt>
                                        <dd class="font-medium text-slate-800">{{ max(0, $coupon->usage_limit - $coupon->usage_count - (int) $coupon->pending_uses_count) }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <div class="mt-auto pt-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <code class="rounded-lg border border-dashed border-[#cbdcc9] bg-[#f6f7f4] px-3 py-2 font-mono text-sm font-bold tracking-wide text-slate-900">{{ $coupon->code }}</code>
                                    <button
                                        type="button"
                                        class="btn-secondary min-h-10 px-3 py-2"
                                        data-copy-code="{{ $coupon->code }}"
                                        aria-label="Copy coupon code {{ $coupon->code }}"
                                    >Copy code</button>
                                </div>
                                <p class="mt-2 min-h-5 text-sm text-emerald-800" data-copy-status aria-live="polite"></p>
                                <a
                                    href="{{ route('get-cart.index', ['discount_code' => $coupon->code]) }}"
                                    class="btn-primary mt-2 w-full"
                                >Use coupon</a>
                                <p class="mt-2 text-xs leading-5 text-slate-500">The code is rechecked against your cart total at checkout.</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <form action="{{ route('logout') }}" method="POST" class="mt-6">
            @csrf
            <button type="submit" class="text-sm font-semibold text-[#a33f34] hover:underline">Log out of your account</button>
        </form>
    </div>
@endsection

@push('script')
    <script>
        document.querySelectorAll('[data-copy-code]').forEach((button) => {
            button.addEventListener('click', async () => {
                const status = button.closest('article')?.querySelector('[data-copy-status]');

                try {
                    await navigator.clipboard.writeText(button.dataset.copyCode);
                    if (status) status.textContent = 'Coupon code copied.';
                } catch {
                    if (status) status.textContent = 'Copy failed. Select the code and copy it manually.';
                }
            });
        });
    </script>
@endpush
