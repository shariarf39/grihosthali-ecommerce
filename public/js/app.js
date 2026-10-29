/* Grihosthali storefront scripts (no build step required). Config comes from window.GH. */
(function () {
    'use strict';

    const GH = window.GH || {};
    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

    /* ---------------- Helpers ---------------- */
    function request(url, data) {
        const opts = {
            method: data ? 'POST' : 'GET',
            headers: { 'X-CSRF-TOKEN': GH.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        };
        if (data) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(data);
        }
        return fetch(url, opts).then(async (res) => {
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                const err = new Error(json.message || 'Something went wrong');
                err.data = json;
                throw err;
            }
            return json;
        });
    }

    function toast(message, type = 'success') {
        let wrap = $('.toast-wrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'toast-wrap';
            document.body.appendChild(wrap);
        }
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.innerHTML = `<i class="fa-solid ${type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'}"></i><span></span>`;
        el.querySelector('span').textContent = message;
        wrap.appendChild(el);
        setTimeout(() => { el.style.transition = 'opacity .3s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
    }
    window.ghToast = toast;

    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const taka = (n) => '৳' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 });

    /* ---------------- Overlay / drawers ---------------- */
    const overlay = $('.overlay');
    const cartSidebar = $('.cart-sidebar');
    const drawer = $('.mobile-drawer');

    function openPanel(panel) {
        closePanels();
        panel && panel.classList.add('open');
        overlay && overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closePanels() {
        [cartSidebar, drawer].forEach((p) => p && p.classList.remove('open'));
        overlay && overlay.classList.remove('show');
        document.body.style.overflow = '';
    }
    overlay && overlay.addEventListener('click', closePanels);
    document.addEventListener('keydown', (e) => e.key === 'Escape' && closePanels());
    $$('[data-close-panel]').forEach((b) => b.addEventListener('click', closePanels));
    $$('[data-open-cart]').forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); openPanel(cartSidebar); }));
    $$('[data-open-menu]').forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); openPanel(drawer); }));

    /* ---------------- Cart state & rendering ---------------- */
    let cart = { count: 0, items: [], subtotal: 0, subtotal_text: '৳0' };

    function renderCart(data) {
        cart = data;
        $$('.cart-count').forEach((el) => { el.textContent = data.count; });
        $$('.fc-count').forEach((el) => { el.textContent = data.count + ' Items'; });
        $$('.fc-total').forEach((el) => { el.textContent = data.subtotal_text; });

        const body = $('.cs-body');
        const foot = $('.cs-foot');
        if (body) {
            if (!data.items.length) {
                body.innerHTML = `<div class="cs-empty"><i class="fa-solid fa-basket-shopping"></i><p>Your cart is empty</p>
                    <a href="${GH.routes.shop}" class="btn">Continue Shopping</a></div>`;
                foot && foot.classList.add('hidden');
            } else {
                body.innerHTML = data.items.map((i) => `
                    <div class="cs-item" data-id="${i.id}">
                        <a href="${i.url}"><img src="${i.image}" alt=""></a>
                        <div class="cs-info">
                            <a href="${i.url}" class="cs-name">${esc(i.name)}</a>
                            <div class="cs-price">${i.price_text} × ${i.qty} = ${i.total_text}</div>
                            <div class="cs-bottom">
                                <div class="mini-qty">
                                    <button type="button" data-cart-dec="${i.id}" aria-label="Decrease"><i class="fa-solid fa-minus"></i></button>
                                    <span>${i.qty}</span>
                                    <button type="button" data-cart-inc="${i.id}" aria-label="Increase"><i class="fa-solid fa-plus"></i></button>
                                </div>
                                <button type="button" class="cs-remove" data-cart-remove="${i.id}" aria-label="Remove"><i class="fa-regular fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>`).join('');
                foot && foot.classList.remove('hidden');
            }
        }
        $$('.cs-subtotal').forEach((el) => { el.textContent = data.subtotal_text; });
        $$('.cs-title-count').forEach((el) => { el.textContent = data.count; });

        // Free delivery progress
        const prog = $('.cs-progress');
        if (prog && GH.freeShippingMin > 0) {
            const left = GH.freeShippingMin - data.subtotal;
            prog.classList.toggle('hidden', !data.items.length);
            $('.cs-progress-text', prog).innerHTML = left > 0
                ? `Add <strong>${taka(left)}</strong> more to get <strong>FREE delivery!</strong>`
                : '🎉 Congratulations! You get <strong>FREE delivery</strong>.';
            $('.bar span', prog).style.width = Math.min(100, (data.subtotal / GH.freeShippingMin) * 100) + '%';
        }

        syncCards();
    }

    /** Product cards show "Add To Cart" or a −/+ quantity box depending on what's in the cart. */
    function syncCards() {
        const qtyById = {};
        cart.items.forEach((i) => { qtyById[i.id] = i.qty; });
        $$('.product-card-action').forEach((action) => {
            const id = action.dataset.id;
            const qty = qtyById[id] || 0;
            const openBtn = $('.open-quantity-btn', action);
            const box = $('.quantity-box', action);
            if (!openBtn || !box) return;
            openBtn.classList.toggle('hidden', qty > 0);
            box.classList.toggle('hidden', qty === 0);
            $('.quantity', box).textContent = qty;
        });
    }

    function cartCall(url, payload, { open = false, silent = false } = {}) {
        return request(url, payload)
            .then((data) => {
                renderCart(data);
                if (open) openPanel(cartSidebar);
                if (data.message && !silent) toast(data.message);
                return data;
            })
            .catch((err) => {
                if (err.data && err.data.items) renderCart(err.data);
                toast(err.message, 'error');
            });
    }

    const qtyOf = (id) => (cart.items.find((i) => String(i.id) === String(id)) || {}).qty || 0;

    document.addEventListener('click', (e) => {
        const t = e.target;

        const addBtn = t.closest('.open-quantity-btn');
        if (addBtn) {
            e.preventDefault();
            const id = addBtn.closest('.product-card-action').dataset.id;
            addBtn.classList.add('loading');
            cartCall(GH.routes.cartAdd, { product_id: id, qty: 1 }, { open: true }).finally(() => addBtn.classList.remove('loading'));
            return;
        }

        const inc = t.closest('.increase-btn, [data-cart-inc]');
        const dec = t.closest('.decrease-btn, [data-cart-dec]');
        if (inc || dec) {
            e.preventDefault();
            const btn = inc || dec;
            const id = btn.dataset.cartInc || btn.dataset.cartDec || btn.closest('.product-card-action').dataset.id;
            const qty = qtyOf(id) + (inc ? 1 : -1);
            cartCall(GH.routes.cartUpdate, { product_id: id, qty: Math.max(0, qty) }, { silent: true });
            return;
        }

        const rm = t.closest('[data-cart-remove]');
        if (rm) {
            e.preventDefault();
            cartCall(GH.routes.cartRemove, { product_id: rm.dataset.cartRemove });
            return;
        }

        // Product details "Add to Cart" with chosen quantity
        const pdAdd = t.closest('[data-add-with-qty]');
        if (pdAdd) {
            e.preventDefault();
            const qtyInput = $('#pd-qty');
            cartCall(GH.routes.cartAdd, { product_id: pdAdd.dataset.addWithQty, qty: parseInt(qtyInput ? qtyInput.value : 1, 10) || 1 }, { open: true });
        }
    });

    if ($('.cart-sidebar') || $('.product-card-action')) {
        request(GH.routes.cartData).then(renderCart).catch(() => {});
    }

    /* ---------------- Wishlist ---------------- */
    let wishlist = new Set((GH.wishlist || []).map(String));

    function syncWishlist() {
        $$('[data-wishlist]').forEach((btn) => {
            const on = wishlist.has(String(btn.dataset.wishlist));
            btn.classList.toggle('active', on);
            const icon = $('i', btn);
            if (icon) icon.className = on ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
            const label = $('[data-label-add]', btn);
            if (label) label.textContent = on ? label.dataset.labelRemove : label.dataset.labelAdd;
        });
        $$('.wishlist-count').forEach((el) => {
            el.textContent = wishlist.size;
            el.classList.toggle('is-empty', wishlist.size === 0);
        });
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-wishlist]');
        if (!btn) return;
        e.preventDefault();
        const id = String(btn.dataset.wishlist);
        btn.classList.add('pulse');
        setTimeout(() => btn.classList.remove('pulse'), 400);
        request(GH.routes.wishlistToggle, { product_id: id })
            .then((data) => {
                data.added ? wishlist.add(id) : wishlist.delete(id);
                syncWishlist();
                toast(data.message);
                // On the wishlist page, removed items disappear.
                const page = btn.closest('[data-wishlist-page]');
                if (page && !data.added) {
                    btn.closest('.product-card').remove();
                    if (!$('.product-card', page)) location.reload();
                }
            })
            .catch((err) => toast(err.message, 'error'));
    });
    syncWishlist();

    /* ---------------- Header "More" dropdown ---------------- */
    $$('.header-more').forEach((wrap) => {
        const trigger = $('button', wrap);
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = wrap.classList.toggle('open');
            trigger.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) wrap.classList.remove('open'); });
    });

    /* ---------------- Product details quantity input ---------------- */
    $$('[data-qty-step]').forEach((btn) => btn.addEventListener('click', () => {
        const input = $('#pd-qty');
        const max = parseInt(input.max, 10) || 999;
        input.value = Math.min(max, Math.max(1, (parseInt(input.value, 10) || 1) + parseInt(btn.dataset.qtyStep, 10)));
        $$('.pd-qty-mirror').forEach((m) => { m.value = input.value; });
    }));
    const pdQty = $('#pd-qty');
    pdQty && pdQty.addEventListener('input', () => $$('.pd-qty-mirror').forEach((m) => { m.value = pdQty.value; }));

    /* ---------------- Live search ---------------- */
    $$('[data-live-search]').forEach((input) => {
        const box = input.closest('form').parentElement.querySelector('.live-search');
        if (!box) return;
        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { box.classList.add('hidden'); return; }
            timer = setTimeout(() => {
                request(GH.routes.suggest + '?q=' + encodeURIComponent(q)).then((items) => {
                    box.innerHTML = items.length
                        ? items.map((p) => `<li><a href="${p.url}"><img src="${p.image}" alt=""><div><div class="ls-name">${esc(p.name)}</div><div class="ls-price">${p.price}</div></div></a></li>`).join('')
                        : '<li class="ls-empty">No products found</li>';
                    box.classList.remove('hidden');
                });
            }, 250);
        });
        document.addEventListener('click', (e) => { if (!input.closest('.header-search').contains(e.target)) box.classList.add('hidden'); });
    });

    /* ---------------- Sliders (Swiper) ---------------- */
    if (window.Swiper) {
        const hero = $('.hero-slider');
        if (hero) {
            new Swiper(hero, {
                loop: $$('.swiper-slide', hero).length > 1,
                autoplay: { delay: 4500, disableOnInteraction: false },
                speed: 700,
                pagination: { el: $('.swiper-pagination', hero), clickable: true },
                navigation: { nextEl: $('.slider-nav.next', hero), prevEl: $('.slider-nav.prev', hero) },
            });
        }

        const catSlider = $('.category-slider');
        if (catSlider) {
            new Swiper(catSlider, {
                slidesPerView: 3, spaceBetween: 12,
                navigation: { nextEl: '.category-slider-wrap .next', prevEl: '.category-slider-wrap .prev' },
                breakpoints: { 480: { slidesPerView: 4 }, 768: { slidesPerView: 5 }, 992: { slidesPerView: 6 }, 1200: { slidesPerView: 8, spaceBetween: 16 } },
            });
        }

        // Product rows glide left one card at a time (like ghorerbazar), pausing while hovered.
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        $$('.product-rail').forEach((rail, index) => {
            const section = rail.closest('section');
            const wrapper = $('.swiper-wrapper', rail);
            const originals = $$('.swiper-slide', wrapper);

            // A seamless loop needs at least two screens of cards; repeat short rows to get there.
            if (originals.length > 1 && originals.length < 10) {
                for (let i = 0; wrapper.children.length < 10; i++) {
                    const clone = originals[i % originals.length].cloneNode(true);
                    clone.setAttribute('aria-hidden', 'true');
                    wrapper.appendChild(clone);
                }
            }
            const canLoop = wrapper.children.length >= 10;

            new Swiper(rail, {
                slidesPerView: 2, spaceBetween: 10, speed: 650,
                loop: canLoop,
                autoplay: reduceMotion || originals.length < 2 ? false : {
                    delay: 2800 + index * 350, disableOnInteraction: false, pauseOnMouseEnter: true,
                },
                navigation: { nextEl: $('.rail-next', section), prevEl: $('.rail-prev', section) },
                breakpoints: { 576: { slidesPerView: 3 }, 768: { slidesPerView: 4, spaceBetween: 14 }, 1200: { slidesPerView: 5, spaceBetween: 16 } },
            });
        });
        syncCards();
        syncWishlist();

        const brands = $('.brand-slider');
        if (brands) {
            new Swiper(brands, {
                slidesPerView: 2, spaceBetween: 12, autoplay: { delay: 2500 }, loop: $$('.swiper-slide', brands).length > 6,
                breakpoints: { 576: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 1200: { slidesPerView: 6, spaceBetween: 16 } },
            });
        }

        const testi = $('.testimonial-slider');
        if (testi) {
            new Swiper(testi, {
                slidesPerView: 1, spaceBetween: 16, autoplay: { delay: 5000 },
                pagination: { el: $('.swiper-pagination', testi), clickable: true },
                breakpoints: { 768: { slidesPerView: 2 }, 1200: { slidesPerView: 3, spaceBetween: 20 } },
            });
        }

        const thumbsEl = $('.pd-thumbs');
        const mainEl = $('.pd-main-swiper');
        if (mainEl) {
            const thumbs = thumbsEl ? new Swiper(thumbsEl, { slidesPerView: 'auto', spaceBetween: 10, watchSlidesProgress: true }) : null;
            new Swiper(mainEl, {
                spaceBetween: 10,
                navigation: { nextEl: $('.next', mainEl), prevEl: $('.prev', mainEl) },
                thumbs: thumbs ? { swiper: thumbs } : undefined,
            });
        }
    }

    /* ---------------- Tabs ---------------- */
    $$('.tab-nav').forEach((nav) => {
        $$('button', nav).forEach((btn) => btn.addEventListener('click', () => {
            const wrap = nav.parentElement;
            $$('button', nav).forEach((b) => b.classList.toggle('active', b === btn));
            $$('.tab-pane', wrap).forEach((p) => p.classList.toggle('active', p.id === btn.dataset.tab));
        }));
    });

    /* ---------------- Checkout live totals ---------------- */
    const checkoutForm = $('#checkout-form');
    if (checkoutForm) {
        const update = () => {
            const checked = $('input[name="shipping_area"]:checked', checkoutForm);
            const subtotal = parseFloat(checkoutForm.dataset.subtotal);
            const discount = parseFloat(checkoutForm.dataset.discount);
            const freeMin = parseFloat(checkoutForm.dataset.freeMin);
            let shipping = checked ? parseFloat(checked.dataset.charge) : 0;
            if (freeMin > 0 && subtotal >= freeMin) shipping = 0;
            $('#os-shipping').textContent = shipping === 0 && checked ? 'Free' : taka(shipping);
            $('#os-total').textContent = taka(Math.max(0, subtotal + shipping - discount));
        };
        $$('input[name="shipping_area"]', checkoutForm).forEach((r) => r.addEventListener('change', update));
        update();
        checkoutForm.addEventListener('submit', () => {
            const btn = $('#place-order-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Placing order...';
        });
    }

    /* ---------------- Misc ---------------- */
    const scrollBtn = $('.scroll-top');
    if (scrollBtn) {
        window.addEventListener('scroll', () => scrollBtn.classList.toggle('show', window.scrollY > 500), { passive: true });
        scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    $$('[data-toggle-filter]').forEach((b) => b.addEventListener('click', () => $('.shop-sidebar').classList.toggle('open')));

    $$('[data-auto-submit]').forEach((el) => el.addEventListener('change', () => el.form.submit()));

    // Subtle fade-up of homepage/listing sections as they scroll into view.
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const targets = $$('main .section, main .features, .promo-banner');
        document.documentElement.classList.add('js-reveal');
        const io = new IntersectionObserver((entries) => entries.forEach((en) => {
            if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
        }), { rootMargin: '0px 0px -60px 0px' });
        targets.forEach((el) => { el.classList.add('reveal'); io.observe(el); });
    }

    // Once the category bar is pinned, reveal its compact search / wishlist / cart icons.
    const bar = $('.header-bottom');
    if (bar) {
        const pin = () => bar.classList.toggle('scrolled', window.scrollY > 140);
        window.addEventListener('scroll', pin, { passive: true });
        pin(); // also correct after a reload part-way down the page
    }

    // Flash messages rendered by the server
    (GH.flash || []).forEach((f) => toast(f.message, f.type));
})();
