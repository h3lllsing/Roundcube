@props(['module', 'action'])
@if(auth()->user()->hasRole('super-admin') || ($module && auth()->user()->canOnModule($module, $action)))
    {{ $slot }}
@endif
