@extends('web.app')

@section('title', 'Payment canceled | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <section class="surface w-full max-w-xl px-6 py-10 text-center sm:px-10" aria-labelledby="payment-cancel-heading">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f2f5f0] text-2xl font-bold text-slate-600" aria-hidden="true">×</span>
            <p class="eyebrow mt-6">Checkout</p>
            <h1 id="payment-cancel-heading" class="section-heading mt-2">Payment canceled</h1>
            <p class="section-copy mx-auto max-w-md">The payment flow was canceled. You can return to the cart to review your order or continue browsing.</p>
            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('get-cart.index') }}" class="btn-primary">Return to cart</a>
                <a href="{{ route('home.index') }}" class="btn-secondary">Back to menu</a>
            </div>
        </section>
    </div>
@endsection
