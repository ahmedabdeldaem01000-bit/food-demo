@extends('web.app')

@section('title', 'Create account | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <div class="w-full max-w-lg">
            <div class="mb-6 text-center">
                <p class="eyebrow">Join Foodify</p>
                <h1 class="section-heading mt-2">Create your account</h1>
                <p class="section-copy">Enter your details to get started.</p>
            </div>

            <form action="{{ route('register.submit') }}" method="POST" class="surface space-y-5 p-5 sm:p-8">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="field-label">Full name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required class="w-full px-3.5 py-3">
                    </div>
                    <div>
                        <label for="phone" class="field-label">Phone number</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required class="w-full px-3.5 py-3">
                    </div>
                    <div>
                        <label for="email" class="field-label">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required class="w-full px-3.5 py-3">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="password" class="field-label">Password</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" required class="w-full px-3.5 py-3">
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full">Continue</button>
                <p class="text-center text-sm text-slate-600">Already have an account? <a href="{{ route('login.show') }}" class="font-semibold text-[#245a38] hover:underline">Log in</a></p>
            </form>
        </div>
    </div>
@endsection
