@if(setting('logo'))
    <img src="{{ upload_url(setting('logo')) }}" alt="{{ setting('site_name', 'Grihosthali') }}">
@else
    <span class="logo-text">
        <span class="leaf"><i class="fa-solid fa-seedling"></i></span>
        <span class="bn-name">গৃহস্থালী</span>
        <span class="en-name">GRIHOSTHALI</span>
    </span>
@endif
