@extends('web.app')

@section('title', 'Log in | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <p class="eyebrow">Welcome back</p>
                <h1 class="section-heading mt-2">Log in to Foodify</h1>
                <p class="section-copy">Sign in to manage your cart and orders.</p>
            </div>

            <form action="{{ route('login.submit') }}" method="POST" class="surface space-y-5 p-5 sm:p-8">
                @csrf
                <div>
                    <label for="email" class="field-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required autofocus class="w-full px-3.5 py-3">
                </div>
                <div>
                    <label for="password" class="field-label">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required class="w-full px-3.5 py-3">
                </div>
                <div class="flex items-center justify-between gap-3 text-sm">
                    <label for="remember" class="inline-flex items-center gap-2 text-slate-600">
                        <input id="remember" type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 accent-[#245a38]">
                        Remember me
                    </label>
                    <a href="{{ route('forget-password.show') }}" class="font-semibold text-[#245a38] hover:underline">Forgot password?</a>
                </div>
                <button type="submit" class="btn-primary w-full">Log in</button>
                <p class="text-center text-sm text-slate-600">New to Foodify? <a href="{{ route('register.show') }}" class="font-semibold text-[#245a38] hover:underline">Create an account</a></p>
            </form>
        </div>
    </div>
@endsection
