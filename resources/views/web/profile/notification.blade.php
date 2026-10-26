@extends('web.app')

@section('title', 'Notifications | Foodify')

@section('content')
    <div class="page-container max-w-4xl">
        <div class="mb-8">
            <p class="eyebrow">Updates</p>
            <h1 class="section-heading mt-2">Notifications</h1>
            <p class="section-copy">The latest updates available for your account.</p>
        </div>

        @if($notifications->isEmpty())
            <div class="surface px-6 py-12 text-center">
                <h2 class="font-semibold text-slate-900">You’re all caught up</h2>
                <p class="mt-2 text-sm text-slate-500">New notifications will appear here.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    @php($isRead = in_array($notification->id, $readIds, true))
                    <article class="surface flex gap-4 p-4 sm:p-5">
                        <img
                            src="{{ $notification->image ? asset('storage/' . $notification->image) : asset('images/image1.jpg') }}"
                            alt=""
                            class="h-14 w-14 shrink-0 rounded-xl object-cover"
                            loading="lazy"
                        >
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-bold uppercase tracking-wide {{ $isRead ? 'text-slate-500' : 'text-[#245a38]' }}">{{ $isRead ? 'Read' : 'New' }}</span>
                                <time datetime="{{ $notification->created_at->toIso8601String() }}" class="text-xs text-slate-500">{{ $notification->created_at->format('M j, Y · g:i A') }}</time>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-800">{{ $notification->body ?? 'New notification' }}</p>
                            @if($notification->description)
                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ $notification->description }}</p>
                            @endif
                            @if($notification->product_id)
                                <a href="{{ route('product.show', $notification->product_id) }}" class="mt-3 inline-block text-sm font-semibold text-[#245a38] hover:underline">View item</a>
                            @endif
                            @if($notification->discount_id)
                                <a href="{{ route('profile.profilePage') }}#my-coupons" class="mt-3 inline-block text-sm font-semibold text-[#245a38] hover:underline">View my coupons</a>
                                <span aria-hidden="true" class="px-1 text-slate-400">·</span>
                                <a href="{{ route('get-cart.index') }}" class="mt-3 inline-block text-sm font-semibold text-[#245a38] hover:underline">Go to cart</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-6">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
