<div class="bg-white shadow rounded-lg p-4 flex flex-col mt-32 md:flex-row gap-4 items-center justify-between w-full">

    <!-- product info -->
    <div class="flex items-center gap-4 w-full md:w-auto">

        <img
            src="{{ asset('storage/'.$item->product->image) }}"
            class="w-20 h-20 object-cover rounded-full">

        <div>
            <h3 class="font-semibold text-lg">
                {{ $item->product->name }}
            </h3>

            <p class="text-sm text-gray-500">
                {{ $item->product->price }} EGP
            </p>
        </div>

    </div>
 

    <div class="flex items-center gap-3">

        <button class="bg-gray-200 w-8 h-8 rounded">-</button>

        <span class="font-semibold">
            {{ $item->quantity }}
        </span>

        <button class="bg-gray-200 w-8 h-8 rounded">+</button>

    </div>

    <!-- total -->

    <div class="font-bold text-green-700">
        {{ $item->product->price * $item->quantity }} EGP
    </div>

</div>