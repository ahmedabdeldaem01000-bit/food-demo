@extends('web.app')

@section('title', 'Payment not completed | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <section class="surface w-full max-w-xl px-6 py-10 text-center sm:px-10" aria-labelledby="payment-failed-heading">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-3xl font-bold text-amber-700" aria-hidden="true">!</span>
            <p class="eyebrow mt-6">Payment update</p>
            <h1 id="payment-failed-heading" class="section-heading mt-2">Payment wasn’t completed</h1>
            <p class="section-copy mx-auto max-w-md">Your payment could not be confirmed. Return to your cart to review the items and try again.</p>
            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('get-cart.index') }}" class="btn-primary">Return to cart</a>
                <a href="{{ route('home.index') }}" class="btn-secondary">Back to menu</a>
            </div>
        </section>
    </div>
@endsection
