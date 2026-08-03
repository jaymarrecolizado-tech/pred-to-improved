@if(is_array($getState()))

    @php
        $route = $getState();
    @endphp

    <div>
        <strong>Origin:</strong> {{ $route['origin'] ?? '-' }}
        <br>
        <strong>Destination:</strong> {{ $route['destination'] ?? '-' }}
    </div>

@else

    {{ $getState() ?? '-' }}

@endif