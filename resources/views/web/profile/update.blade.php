@extends('web.app')

@section('title', 'Edit profile | Foodify')

@section('content')
    <div class="page-container max-w-3xl">
        <a href="{{ route('profile.profilePage') }}" class="text-sm font-semibold text-[#245a38] hover:underline">← Back to account</a>
        <div class="mb-7 mt-5">
            <p class="eyebrow">Account details</p>
            <h1 class="section-heading mt-2">Edit your profile</h1>
            <p class="section-copy">Keep your contact information up to date.</p>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                <p class="font-semibold">Please review the following:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.personalEdit') }}" method="POST" enctype="multipart/form-data" class="surface space-y-5 p-5 sm:p-8">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="field-label">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" autocomplete="name" required class="w-full px-3.5 py-3">
                    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="field-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" autocomplete="email" required class="w-full px-3.5 py-3">
                    @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="field-label">Phone number</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" autocomplete="tel" required class="w-full px-3.5 py-3">
                    @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="birth_date" class="field-label">Date of birth</label>
                    <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', auth()->user()->birth_date) }}" required class="w-full px-3.5 py-3">
                    @error('birth_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="field-label">Address</label>
                    <textarea id="address" name="address" rows="3" autocomplete="street-address" required class="w-full px-3.5 py-3">{{ old('address', auth()->user()->address) }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="photo" class="field-label">Profile photo <span class="font-normal text-slate-500">(optional)</span></label>
                    <input id="photo" type="file" name="photo" accept="image/*" class="w-full rounded-xl border border-[#d9e0da] bg-white px-3 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#edf4e9] file:px-3 file:py-2 file:font-semibold file:text-[#245a38]">
                    @error('photo')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#e7ebe5] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('profile.profilePage') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save changes</button>
            </div>
        </form>
    </div>
@endsection
