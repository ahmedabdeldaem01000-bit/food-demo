@extends('web.app')
@section('content')

<div class="flex flex-wrap flex-row items-center justify-center content-center w-full h-screen">
    <img src="{{ asset('images/land/success.png') }}" alt="">
</div>
@endsection
@push('scripts')
<script>
    // تغيير حالة سجل التصفح بحيث ميرجعش لصفحة الـ PayPal تاني
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // منع زرار الـ Back تماماً (اختياري)
    window.onpopstate = function() {
        window.history.pushState(null, "", window.location.href);
    };
</script>
@endpush