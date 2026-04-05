<div
    class="w-full  bg-white shadow-md py-4 px-6 fixed bottom-0 md:top-0 left-0 z-10 h-20 flex items-center justify-between flex-wrap flex-row  md:rounded-t-2xl">
    <div class="hidden  w-fit md:flex items-center bg-[#F2FFF6] rounded-full px-4 py-3 shadow-md">

        <!-- icon -->
        <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>

        <!-- input -->
        <input type="text" placeholder="What Do You Want To Eat ?"
            class="bg-transparent outline-none w-full text-gray-700 placeholder-gray-500">
    </div>

    <ul class=" w-fit flex gap-2   justify-center items-center">

        @guest
            <li class="justify-center flex flex-col items-center">
                <p class="md:hidden flex"><i class="fa-solid fa-house"></i></p>
                <a href="{{ route('home.index') }}">Home</a>
            </li>
            <li class="justify-center flex flex-col items-center">
                <p class="md:hidden flex"> <i class="fa-solid fa-layer-group"></i></p>
                <a href="{{ route('category.index') }}">Category</a>
            </li>
            <li class="justify-center flex flex-col items-center">
                <p class="md:hidden flex"> <i class="fa-regular fa-heart"></i></p>
                <a href="{{ route('favorite.showFavorite') }}">Favorite</a>
            </li>
            <li class="justify-center flex flex-col items-center">
                <p class="md:hidden flex"> <i class="fa-solid fa-arrow-right-to-bracket"></i></p>
                <a href="{{ route('login.show') }}">Login</a>
            </li>
            <li class="justify-center flex flex-col items-center">
                <p class="md:hidden flex"><i class="fa-regular fa-user"></i></p>
                <a href="{{ route('register.show') }}">Register</a>
            </li>

        @endguest


        @auth
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">
                            Logout
                        </button>
                    </form>
                </li>

                <li>
                    <a href="{{ route('home.index') }}">Home</a>
                </li>

                <li>

                    <a href="{{ route('profile.profilePage') }}">Profile</a>
                </li>
                <li class="justify-center flex flex-col items-center">
                    <p class="md:hidden flex"> <i class="fa-solid fa-layer-group"></i></p>
                    <a href="{{ route('category.index') }}">Category</a>
                </li>
                <li class="justify-center flex flex-col items-center">
                    <p class="md:hidden flex"> <i class="fa-regular fa-heart"></i></p>
                    <a href="{{ route('favorite.showFavorite') }}">Favorite</a>
                </li>



            </ul>

            <div class="  w-fit flex-row flex-wrap gap-4 justify-end  md:flex hidden  items-end">
                <a href="{{ route('get-cart.index') }}"><i class="fa-solid fa-cart-shopping"></i> <span class="cart-count">
                        {{ $cartCount }}
                    </span></a>
                <a href="{{ route('notification.index') }}"><i class="fa-regular fa-bell"></i><span class="cart-count">
                        {{ $unreadCount }}
                    </span></a>

            </div>

        @endauth
</div>