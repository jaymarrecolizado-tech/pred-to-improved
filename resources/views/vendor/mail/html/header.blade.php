@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'TRAVEL ORDER')
                <img src="{{ asset('images/DICT_logo.png') }}" class="logo" alt="Laravel Logo">
            @else
                {!! $slot !!}
            @endif
        </a>
    </td>
</tr>
