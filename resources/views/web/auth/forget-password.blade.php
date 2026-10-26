@extends('web.app')

@section('title', 'Reset password | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <section class="surface w-full max-w-md p-5 sm:p-8" aria-labelledby="reset-heading">
            <p class="eyebrow">Account recovery</p>
            <h1 id="reset-heading" class="section-heading mt-2">Reset your password</h1>
            <p class="section-copy">Enter the email address linked to your account to continue.</p>
            <form action="{{ route('otp-forget-password.submit') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="email" class="field-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required class="w-full px-3.5 py-3">
                </div>
                <button type="submit" class="btn-primary w-full">Continue</button>
                <a href="{{ route('login.show') }}" class="block text-center text-sm font-semibold text-[#245a38] hover:underline">Back to log in</a>
            </form>
        </section>
    </div>
@endsection
