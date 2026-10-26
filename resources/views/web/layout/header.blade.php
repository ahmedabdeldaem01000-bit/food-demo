<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow">
    Skip to content
</a>
<header class="sticky top-0 z-40 border-b border-[#e7ebe5] bg-white/95 backdrop-blur">
    <div class="page-container flex min-h-[4.5rem] items-center justify-between gap-4">
        <a href="{{ route('home.index') }}" class="flex shrink-0 items-center gap-2.5 text-xl font-extrabold tracking-tight text-[#245a38]" aria-label="Foodify home">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#edf4e9] text-sm" aria-hidden="true">F</span>
            <span>Foodify</span>
        </a>

        <nav aria-label="Main navigation" class="hidden items-center gap-1 md:flex">
            <a href="{{ route('home.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold transition hover:bg-[#f2f5f0]', 'bg-[#edf4e9] text-[#245a38]' => request()->routeIs('home.index'), 'text-slate-600' => !request()->routeIs('home.index')])>Home</a>
            <a href="{{ route('category.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold transition hover:bg-[#f2f5f0]', 'bg-[#edf4e9] text-[#245a38]' => request()->routeIs('category.*'), 'text-slate-600' => !request()->routeIs('category.*')])>Menu</a>
            @auth
                <a href="{{ route('profile.showOrders') }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold transition hover:bg-[#f2f5f0]', 'bg-[#edf4e9] text-[#245a38]' => request()->routeIs('profile.showOrders'), 'text-slate-600' => !request()->routeIs('profile.showOrders')])>My orders</a>
                <a href="{{ route('favorite.showFavorite') }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold transition hover:bg-[#f2f5f0]', 'bg-[#edf4e9] text-[#245a38]' => request()->routeIs('favorite.showFavorite'), 'text-slate-600' => !request()->routeIs('favorite.showFavorite')])>Favorites</a>
            @endauth
        </nav>

        <div class="flex shrink-0 items-center gap-2">
            @auth
                <a href="{{ route('get-cart.index') }}" class="relative inline-flex h-10 items-center gap-2 rounded-xl border border-[#e7ebe5] px-3 text-sm font-semibold text-slate-700 transition hover:bg-[#f6f7f4]" aria-label="Cart, {{ $cartCount }} items">
                    <span aria-hidden="true">Cart</span>
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-[#245a38] px-1.5 py-0.5 text-xs font-bold text-white">{{ $cartCount }}</span>
                </a>
                <a href="{{ route('notification.index') }}" class="relative inline-flex h-10 items-center rounded-xl px-2.5 text-sm font-semibold text-slate-700 transition hover:bg-[#f2f5f0]" aria-label="Notifications, {{ $unreadCount }} unread">
                    <span aria-hidden="true">Alerts</span>
                    @if($unreadCount > 0)
                        <span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-[#b54736] px-1.5 py-0.5 text-xs font-bold text-white">{{ $unreadCount }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.profilePage') }}" class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-[#f2f5f0] sm:inline-flex">Account</a>
                <form action="{{ route('logout') }}" method="POST" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-[#f2f5f0] hover:text-[#245a38]">Log out</button>
                </form>
            @else
                <a href="{{ route('login.show') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-[#f2f5f0]">Log in</a>
                <a href="{{ route('register.show') }}" class="btn-primary min-h-10 px-4 py-2">Create account</a>
            @endauth
        </div>
    </div>

    <nav aria-label="Mobile navigation" class="flex gap-1 overflow-x-auto border-t border-[#f0f2ee] px-4 py-2 md:hidden">
        <a href="{{ route('home.index') }}" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-[#f2f5f0]">Home</a>
        <a href="{{ route('category.index') }}" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-[#f2f5f0]">Menu</a>
        @auth
            <a href="{{ route('profile.showOrders') }}" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-[#f2f5f0]">My orders</a>
            <a href="{{ route('favorite.showFavorite') }}" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-[#f2f5f0]">Favorites</a>
            <a href="{{ route('profile.profilePage') }}" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-[#f2f5f0]">Account</a>
            <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-[#f2f5f0]">Log out</button>
            </form>
        @endauth
    </nav>
</header>
