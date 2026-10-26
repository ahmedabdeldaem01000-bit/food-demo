@include("web.layout.head")

@include("web.layout.header")

<main id="main-content" class="site-main">
    @if ($errors->any())
        <div class="page-container mb-6" role="alert">
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <p class="font-semibold">Please check the form and try again.</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="page-container mb-6" role="status" aria-live="polite">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="page-container mb-6" role="alert">
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                {{ session('error') }}
            </div>
        </div>
    @endif
    @yield('content')
</main>

@include("web.layout.footer")