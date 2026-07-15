@props(['module', 'action'])
@if($action === 'reveal')
    @if(auth()->user()->canRevealCredentialsFor($module))
        {{ $slot }}
    @endif
@else
    @if(auth()->user()->hasRole('super-admin') || ($module && auth()->user()->canOnModule($module, $action)))
        {{ $slot }}
    @endif
@endif
