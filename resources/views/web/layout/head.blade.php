<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" integrity="sha512-Zcn6bjR/8RZbLEpLIeOwNtzREBAJnUKESxces60Mpoj+2okopSAcSUIUOseddDm0cxnGQzxIR7vJgsLZbdLE3w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
 @livewireStyles
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <title>Foodify</title>
</head>
<style>
#map{
    height: calc(100% - 60px);
}
header{
    padding: 15px;
    background: #f0f0f0;
    font-size: 1.2rem;
}
.info{
    position: absolute;
    top:70px;
    right: 10px;
    background: #fff;
    padding: 10px;
    border-radius: 6px
}
</style>

<body
    class=" min-h-[100vh] relative bg-[#F6F6F6]   overflow-x-hidden flex flex-row flex-wrap content-center justify-between w-full items-center">

    <div
        class=" rounded-full absolute flex items-end md:content-center content-end flex-wrap flex-row justify-center bg-green-800 w-123 h-113.75 -top-84 -left-32 -z-1 md:-top-24 md:-left-32">
        <div class="md:hidden py-2 w-fit gap-2 md:w-full flex flex-wrap flex-col items-center content-center justify-center">
            <div class="flex flex-row flex-wrap gap-4 justify-end  w-full items-end">
                <i class="fa-solid fa-cart-shopping"></i>
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="">
                <input type="text" placeholder="What Do You Wont To Eat"
                    class="bg-white px-4 py-2 w-full text-black rounded-md border-none text-sm">
            </div>
            <div class="">
                <h1 class="text-xl font-semibold text-white">
                    @yield('page-title')
                </h1>
            </div>


        </div>
    </div>
    <div class=" rounded-full fixed bg-green-800 w-123 h-113.75 md:flex hidden  -z-1 md:top-100 md:-right-40"></div>
    <div class="hidden w-screen absolute top-0 left-0 -z-1">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#065e21" fill-opacity="1"
                d="M0,320L40,282.7C80,245,160,171,240,122.7C320,75,400,53,480,74.7C560,96,640,160,720,154.7C800,149,880,75,960,53.3C1040,32,1120,64,1200,69.3C1280,75,1360,53,1400,42.7L1440,32L1440,0L1400,0C1360,0,1280,0,1200,0C1120,0,1040,0,960,0C880,0,800,0,720,0C640,0,560,0,480,0C400,0,320,0,240,0C160,0,80,0,40,0L0,0Z">
            </path>
        </svg>

    </div>