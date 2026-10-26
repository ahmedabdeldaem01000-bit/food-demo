@extends('web.app')

@section('title', 'Order confirmed | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <section class="surface w-full max-w-xl px-6 py-10 text-center sm:px-10" aria-labelledby="payment-success-heading">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-3xl font-bold text-emerald-700" aria-hidden="true">✓</span>
            <p class="eyebrow mt-6">Payment complete</p>
            <h1 id="payment-success-heading" class="section-heading mt-2">Your order is confirmed</h1>
            <p class="section-copy mx-auto max-w-md">Thanks for ordering with Foodify. You can review the latest order details in your account.</p>
            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('profile.showOrders') }}" class="btn-primary">View my orders</a>
                <a href="{{ route('home.index') }}" class="btn-secondary">Back to menu</a>
            </div>
        </section>
    </div>
@endsection
