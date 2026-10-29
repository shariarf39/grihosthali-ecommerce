{{-- "More" dropdown in the header (Wishlists, FAQs, About Us, Call Us, WhatsApp). --}}
<ul class="more-menu">
    <li><a href="{{ route('wishlist.index') }}"><i class="fa-regular fa-heart"></i> Wishlists</a></li>
    <li><a href="{{ route('page.show', 'faqs') }}"><i class="fa-regular fa-circle-question"></i> FAQs</a></li>
    <li><a href="{{ route('page.show', 'about-us') }}"><i class="fa-regular fa-id-badge"></i> About Us</a></li>
    <li><a href="{{ route('contact') }}"><i class="fa-regular fa-envelope"></i> Contact Us</a></li>
    @if(setting('hotline', setting('phone')))
        <li><a href="tel:{{ setting('hotline', setting('phone')) }}"><i class="fa-solid fa-phone-volume"></i> Call Us</a></li>
    @endif
    @if(setting('whatsapp'))
        <li><a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" style="color:#25d366"></i> WhatsApp</a></li>
    @endif
</ul>
