@extends('web.app')
@section('page-title')
    Update profile
@endsection
@section('content')
    <div class="justify-center flex flex-wrap flex-row items-center content-center w-full mt-34">
        <div class="min-h-screen  bg-gray-100">


            <!-- Profile Card -->
            <div class="max-w-sm mx-auto -mt-12 bg-white rounded-2xl shadow p-6">

                <!-- Avatar -->
                <div class="flex flex-col items-center">
                    <img src="{{ asset(auth()->user()->photo?? asset('images/default.png')) }}"
                        class="w-24 h-24 rounded-full object-cover border-4 border-white shadow">

                    <h3 class="mt-3 text-lg font-semibold">
                        {{ auth()->user()->name }}
                    </h3>
                </div>
   @if ($errors->any())
      <div class="w-full p-3 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
          <ul class="list-disc pl-5">
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
  @endif
                <!-- Form -->
                <form action="{{ route('profile.personalEdit') }}" method="POST" enctype="multipart/form-data"
                    class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="text-sm text-gray-600">Name</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}"
                            class="w-full mt-1 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Email</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}"
                            class="w-full mt-1 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Phone</label>
                        <input type="text" name="phone" value="{{ auth()->user()->phone }}"
                            class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-green-600">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Birth Date</label>
                        <input type="date" name="birth_date" value="{{ auth()->user()->birth_date }}"
                            class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-green-600">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Address</label>
                        <textarea name="address" rows="3"
                            class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-green-600">{{ auth()->user()->address }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Profile Image</label>
                        <input type="file" name="photo" class="w-full mt-1 p-2 border rounded-lg">
                    </div>

                    <button
                        class="w-full bg-green-700 text-white py-3 rounded-lg font-semibold hover:bg-green-800 transition">
                        Update Profile
                    </button>

                </form>

            </div>

        </div>
    </div>
@endsection