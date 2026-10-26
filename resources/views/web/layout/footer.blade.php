<footer class="border-t border-[#e7ebe5] bg-white">
    <div class="page-container grid gap-8 py-8 sm:grid-cols-2 sm:items-center">
        <div>
            <a href="{{ route('home.index') }}" class="text-lg font-extrabold tracking-tight text-[#245a38]">Foodify</a>
            <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">Browse the menu, save your favorites, and keep track of your orders.</p>
        </div>
        <nav aria-label="Footer navigation" class="flex flex-wrap gap-x-5 gap-y-2 text-sm font-medium text-slate-600 sm:justify-end">
            <a href="{{ route('home.index') }}" class="hover:text-[#245a38]">Home</a>
            <a href="{{ route('category.index') }}" class="hover:text-[#245a38]">Menu</a>
            @auth
                <a href="{{ route('profile.showOrders') }}" class="hover:text-[#245a38]">My orders</a>
                <a href="{{ route('profile.profilePage') }}" class="hover:text-[#245a38]">Account</a>
            @else
                <a href="{{ route('login.show') }}" class="hover:text-[#245a38]">Log in</a>
                <a href="{{ route('register.show') }}" class="hover:text-[#245a38]">Create account</a>
            @endauth
        </nav>
    </div>
    <div class="border-t border-[#f0f2ee] py-4 text-center text-xs text-slate-500">
        © {{ date('Y') }} Foodify
    </div>
</footer>
@livewireScripts
@stack('script')
</body>
</html>
