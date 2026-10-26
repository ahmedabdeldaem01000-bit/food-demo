@extends('web.app')

@section('title', 'Verify your account | Foodify')

@section('content')
    <div class="page-container flex justify-center">
        <section class="surface w-full max-w-md p-5 sm:p-8" aria-labelledby="otp-heading">
            <p class="eyebrow">Account verification</p>
            <h1 id="otp-heading" class="section-heading mt-2">Enter your verification code</h1>
            <p class="section-copy">Enter the 4-digit code sent to your registered phone number.</p>
            <form method="POST" action="{{ route('otp.verify') }}" class="mt-6">
                @csrf
                <fieldset>
                    <legend class="field-label">One-time code</legend>
                    <div class="flex justify-between gap-2">
                        @for($i = 0; $i < 4; $i++)
                            <input
                                type="text"
                                name="code[]"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="[0-9]"
                                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                aria-label="Digit {{ $i + 1 }}"
                                required
                                class="h-14 w-14 text-center text-xl font-bold sm:h-16 sm:w-16"
                            >
                        @endfor
                    </div>
                </fieldset>
                <button type="submit" class="btn-primary mt-6 w-full">Verify code</button>
            </form>
        </section>
    </div>
@endsection
