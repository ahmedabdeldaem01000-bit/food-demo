<div>
    <h1>Header</h1>

    <ul class="flex gap-4">

        @guest
            <li>
                <a href="{{ route('register.show') }}">Register</a>
            </li>

            <li>
                <a href="{{ route('login.show') }}">Login</a>
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
        @endauth


        <li>
            <a href="{{ route('home.show') }}">Home</a>
        </li>

        <li>
            <a href="{{ route('forget-password.show') }}">Forget Password</a>
        </li>

    </ul>
</div>