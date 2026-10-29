<div class="breadcrumb-bar">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Home</a></li>
            @foreach($items as $label => $url)
                <li>@if($url)<a href="{{ $url }}">{{ $label }}</a>@else{{ $label }}@endif</li>
            @endforeach
        </ul>
    </div>
</div>
