<h1>{{ $offer['name'] }}</h1>
@if($offer['description'])
    <p>{{ $offer['description'] }}</p>
@endif
<p>
    @if($offer['type'] === 'percentage')
        Save {{ number_format($offer['value'], 0) }}%.
    @else
        Save {{ number_format($offer['value'], 2) }} EGP.
    @endif
</p>
<p>Use code <strong>{{ $offer['code'] }}</strong> at checkout.</p>
@if($offer['starts_at'])
    <p>Starts: {{ $offer['starts_at'] }}</p>
@endif
@if($offer['ends_at'])
    <p>Ends: {{ $offer['ends_at'] }}</p>
@endif
<p><a href="{{ $offer['url'] }}">View your coupons</a></p>
