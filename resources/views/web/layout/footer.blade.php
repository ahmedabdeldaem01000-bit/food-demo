<footer class="bg-gray-900 text-white mt-10 w-full">
    <div class="container mx-auto px-6 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Restaurant Info -->
        <div>
            <h2 class="text-xl font-bold mb-4">Phon Restaurant</h2>
            <p class="text-gray-300 mb-2">
                The best Eastern and Western dishes with a modern twist.
            </p>
            <p class="text-gray-400">
                📍 Address: 123 Restaurant Street, City
            </p>
            <p class="text-gray-400">
                📞 Phone: <a href="tel:+201234567890" class="hover:text-yellow-500">+20 123 456 7890</a>
            </p>
        </div>

        <!-- Quick Links -->
        <div>
            <h2 class="text-xl font-bold mb-4">Quick Links</h2>
            <ul class="space-y-2">
                <li><a href="{{ route('home.index') }}" class="hover:text-yellow-500">Home</a></li>
                <li><a href="#" class="hover:text-yellow-500">Categories</a></li>
                <li><a href="{{ route('login.show') }}" class="hover:text-yellow-500">Login</a></li>
                <li><a href="{{ route('register.show') }}" class="hover:text-yellow-500">Register</a></li>
            </ul>
        </div>

        <!-- Social / External Links -->
        <div>
            <h2 class="text-xl font-bold mb-4">Follow Us</h2>
            <ul class="flex space-x-4">
                <li><a href="https://www.facebook.com" target="_blank" class="hover:text-blue-600">Facebook</a></li>
                <li><a href="https://www.instagram.com" target="_blank" class="hover:text-pink-500">Instagram</a></li>
                <li><a href="https://www.twitter.com" target="_blank" class="hover:text-blue-400">Twitter</a></li>
                <li><a href="https://www.tiktok.com" target="_blank" class="hover:text-black">TikTok</a></li>
            </ul>
        </div>

    </div>

    <div class="border-t border-gray-700 mt-8 pt-4 text-center text-gray-500 text-sm">
        © {{ date('Y') }} Phon Restaurant. All rights reserved.
    </div>
</footer>
 <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

   <!-- أي سكربت إضافي -->
    @stack('script')

</body>
</html>
