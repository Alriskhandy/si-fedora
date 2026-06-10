@if (auth()->check())
    @include('errors.404-dashboard')
@else
    @include('errors.404-guest')
@endif
