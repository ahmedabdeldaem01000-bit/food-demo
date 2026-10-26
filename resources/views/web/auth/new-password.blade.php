@extends('web.app')

@section('title', 'Choose a new password | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <section class="surface w-full max-w-md p-5 sm:p-8" aria-labelledby="new-password-heading">
            <p class="eyebrow">Account recovery</p>
            <h1 id="new-password-heading" class="section-heading mt-2">Choose a new password</h1>
            <p class="section-copy">Use a password you haven’t used for this account before.</p>
            <form action="{{ route('update-new-password.submit') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="password" class="field-label">New password</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required class="w-full px-3.5 py-3">
                </div>
                <div>
                    <label for="password_confirmation" class="field-label">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="w-full px-3.5 py-3">
                </div>
                <input type="hidden" name="user_id" value="{{ $userId }}">
                <button type="submit" class="btn-primary w-full">Save password</button>
            </form>
        </section>
    </div>
@endsection
