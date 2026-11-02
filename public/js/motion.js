/* Grihosthali — motion & interaction: preloader, custom cursor, scroll reveals,
   magnetic buttons, 3D tilt, hero parallax, 3D showcase, product lens zoom, honey/leaf cursor with glow or sparkle trail. */
(function () {
    'use strict';

    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));
    const html = document.documentElement;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // True when any mouse or touchpad is present — also on touchscreen laptops, whose *primary* pointer
    // may be reported as touch even though people use the touchpad.
    const finePointer = window.matchMedia('(any-pointer: fine)').matches || window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    if (!reduceMotion) html.classList.add('js-motion');

    /* ---------------- Preloader ---------------- */
    const preloader = $('.preloader');
    let loadedDone = false;
    const finishLoading = () => {
        if (loadedDone) return;
        loadedDone = true;
        html.classList.add('is-loaded');
        setTimeout(() => preloader && preloader.remove(), 700);
        // Deferred so it always runs after the elements below have been tagged.
        setTimeout(startReveals, 0);
    };
    if (html.classList.contains('no-preloader') || reduceMotion) {
        finishLoading();
    } else if (document.readyState === 'complete') {
        setTimeout(finishLoading, 350);
    } else {
        window.addEventListener('load', () => setTimeout(finishLoading, 250));
        setTimeout(finishLoading, 2600); // never keep visitors waiting on slow images
    }

    /* ---------------- Scroll progress bar ---------------- */
    const progress = document.createElement('div');
    progress.className = 'scroll-progress';
    document.body.appendChild(progress);
    const updateProgress = () => {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        progress.style.transform = `scaleX(${max > 0 ? Math.min(1, window.scrollY / max) : 0})`;
    };
    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();

    /* ---------------- Reveal on scroll (staggered) ---------------- */
    const revealGroups = [
        ['.features .feature-item', 'up'],
        ['.category-section-head', 'up'],
        ['.section-head', 'left'],
        ['.category-slider .swiper-slide', 'zoom'],
        ['.product-rail', 'up'],
        ['.product-grid > .product-card', 'up'],
        ['.brand-slider', 'fade'],
        ['.promo-banner .pb-text > *', 'up'],
        ['.promo-banner .pb-img', 'right'],
        ['.testimonial-slider', 'up'],
        ['.showcase-slider', 'zoom'],
        ['.footer-top > *', 'up'],
        ['.footer-bottom', 'fade'],
        ['.breadcrumb-bar .breadcrumb', 'left'],
        ['.shop-sidebar .sidebar-box', 'left'],
        ['.shop-toolbar', 'up'],
        ['.pd-gallery', 'left'],
        ['.pd-info > *', 'up'],
        ['.product-tabs', 'up'],
        ['.checkout-card', 'up'],
        ['.cart-page > *', 'up'],
        ['.auth-box, .success-box', 'zoom'],
        ['.page-content > *', 'up'],
        ['.contact-grid > *', 'up'],
        ['.contact-info-item', 'left'],
        ['.account-card', 'up'],
        ['.empty-state', 'zoom'],
        ['.return-page > *', 'up'],
    ];
    let io = null;

    function tagReveals(root = document) {
        revealGroups.forEach(([selector, kind]) => {
            $$(selector, root).forEach((el) => {
                if (el.hasAttribute('data-reveal') && el.dataset.revealTagged) return;
                if (!el.hasAttribute('data-reveal')) el.setAttribute('data-reveal', kind);
                el.dataset.revealTagged = '1';
                // Stagger siblings: each next item starts a little later (capped so long grids don't drag).
                const siblings = Array.from(el.parentElement.children).filter((c) => c.matches(selector));
                el.style.setProperty('--d', `${Math.min(siblings.indexOf(el), 8) * 80}ms`);
            });
        });
        $$('[data-reveal]:not([data-reveal-tagged])', root).forEach((el) => { el.dataset.revealTagged = '1'; });
    }

    /* Headings: split into words so they can rise in one after another. */
    function splitHeadings() {
        $$('.category-section-head h3, .section-head h3, .showcase-title, .promo-banner h3, .page-title, .shop-toolbar h1, .pd-info h1, .success-box h2, .auth-box h2').forEach((h) => {
            if (h.classList.contains('split-ready')) return;
            let i = 0;
            const wrap = (node) => {
                const outer = document.createElement('span');
                outer.className = 'split-word';
                const inner = document.createElement('span');
                inner.style.setProperty('--i', i++);
                inner.appendChild(node);
                outer.appendChild(inner);
                return outer;
            };
            Array.from(h.childNodes).forEach((node) => {
                if (node.nodeType === Node.TEXT_NODE) {
                    const parts = node.textContent.split(/(\s+)/);
                    const frag = document.createDocumentFragment();
                    parts.forEach((part) => {
                        if (!part) return;
                        frag.appendChild(/^\s+$/.test(part) ? document.createTextNode(part) : wrap(document.createTextNode(part)));
                    });
                    h.replaceChild(frag, node);
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    h.replaceChild(wrap(node.cloneNode(true)), node);
                }
            });
            h.classList.add('split-ready');
            h.setAttribute('aria-label', h.textContent.replace(/\s+/g, ' ').trim());
        });
    }

    function startReveals() {
        if (io) return;
        if (reduceMotion || !('IntersectionObserver' in window)) {
            $$('[data-reveal]').forEach((el) => el.classList.add('revealed'));
            return;
        }
        io = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add(entry.target.classList.contains('split-ready') ? 'words-in' : 'revealed');
            io.unobserve(entry.target);
        }), { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
        $$('[data-reveal], .split-ready').forEach((el) => io.observe(el));

        // Safety net: if the observer never fires (old browsers, some in-app webviews), reveal whatever
        // is on screen by measuring positions directly, so content can never stay hidden.
        let last = 0;
        const sweep = () => {
            last = Date.now();
            $$('[data-reveal]:not(.revealed), .split-ready:not(.words-in)').forEach((el) => {
                const r = el.getBoundingClientRect();
                if (r.top < window.innerHeight * 0.95 && r.bottom > 0) {
                    el.classList.add(el.classList.contains('split-ready') ? 'words-in' : 'revealed');
                }
            });
        };
        setTimeout(sweep, 1200);
        window.addEventListener('scroll', () => { if (Date.now() - last > 200) setTimeout(sweep, 250); }, { passive: true });
    }

    // Menu items drop in one by one on load.
    $$('.header-bottom .main-menu > li').forEach((li, i) => li.style.setProperty('--i', i));

    if (!reduceMotion) {
        tagReveals();
        splitHeadings();
    }

    /* ---------------- Custom cursor ---------------- */
    // Style comes from Admin → Store Settings: "honey" (golden arrow + hand), "leaf", or "off" (system cursor).
    // The cursor switches on at the first real mouse/touchpad movement (not by guessing the device type),
    // and the system arrow is only hidden once ours has been drawn. Touching the screen switches it off again.
    const cursorStyle = document.body.dataset.cursorStyle || 'honey';
    const cursorDot = $('.cursor-dot');
    const cursorRing = $('.cursor-ring');
    if (cursorStyle !== 'off' && cursorDot && cursorRing) {
        const dot = cursorDot;
        const ring = cursorRing;
        const styleClass = cursorStyle === 'leaf' ? 'cs-leaf' : 'cs-honey';
        let active = false;
        let mx = -100, my = -100, rx = -100, ry = -100, tilt = 0, lastMx = null;
        const ease = reduceMotion ? 1 : 0.2;

        const loop = () => {
            if (!active) return;
            rx += (mx - rx) * ease;
            ry += (my - ry) * ease;
            ring.style.transform = `translate3d(${rx}px, ${ry}px, 0)`;
            // The honey arrow leans into the direction of travel and settles back when the mouse stops.
            if (!reduceMotion && lastMx !== null) {
                const target = Math.max(-16, Math.min(16, (mx - lastMx) * 1.4));
                tilt += (target - tilt) * 0.18;
                dot.style.setProperty('--tilt', `${tilt.toFixed(2)}deg`);
            }
            lastMx = mx;
            requestAnimationFrame(loop);
        };

        const activate = (x, y) => {
            mx = rx = x; my = ry = y;
            dot.style.transform = `translate3d(${x}px, ${y}px, 0)`;
            ring.style.transform = `translate3d(${x}px, ${y}px, 0)`;
            html.classList.add('has-cursor', styleClass);
            html.classList.remove('cursor-hidden');
            active = true;
            requestAnimationFrame(loop);
            document.dispatchEvent(new CustomEvent('gh:cursor-on'));
        };
        const deactivate = () => {
            active = false;
            html.classList.remove('has-cursor', 'c-hover', 'c-down');
        };

        const onMove = (x, y) => {
            if (!active) { activate(x, y); return; }
            mx = x; my = y;
            dot.style.transform = `translate3d(${x}px, ${y}px, 0)`;
            html.classList.remove('cursor-hidden');
        };

        if (window.PointerEvent) {
            document.addEventListener('pointermove', (e) => {
                if (e.pointerType === 'touch') return;
                onMove(e.clientX, e.clientY);
            }, { passive: true });
            document.addEventListener('pointerdown', (e) => {
                if (e.pointerType === 'touch') deactivate();
            }, { passive: true });
        } else {
            document.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY), { passive: true });
            document.addEventListener('touchstart', deactivate, { passive: true });
        }

        document.addEventListener('mouseleave', () => html.classList.add('cursor-hidden'));
        document.addEventListener('mousedown', () => html.classList.add('c-down'));
        document.addEventListener('mouseup', () => html.classList.remove('c-down'));
        window.addEventListener('blur', () => html.classList.add('cursor-hidden'));

        // No text bubbles on the cursor: clickable things simply get the hand (honey) or a wider ring (leaf).
        document.addEventListener('mouseover', (e) => {
            const t = e.target;
            if (!active || !t.closest) return;
            const text = t.closest('input, textarea, select, [contenteditable]');
            html.classList.toggle('c-text', !!text);
            const clickable = !!t.closest('a, button, [role="button"], label, .swiper-pagination-bullet, summary, .showcase-slider');
            html.classList.toggle('c-hover', !text && clickable);
        });
    }

    /* ---------------- Magnetic buttons ---------------- */
    if (finePointer && !reduceMotion) {
        $$('[data-magnetic], .hero-caption .btn, .promo-banner .btn, .float-whatsapp, .showcase-nav, .slider-nav').forEach((el) => {
            const strength = el.matches('.float-whatsapp, .showcase-nav, .slider-nav') ? 0.35 : 0.25;
            el.addEventListener('mousemove', (e) => {
                const r = el.getBoundingClientRect();
                const x = (e.clientX - r.left - r.width / 2) * strength;
                const y = (e.clientY - r.top - r.height / 2) * strength;
                el.style.transform = `translate(${x}px, ${y}px)`;
            });
            el.addEventListener('mouseleave', () => { el.style.transform = ''; });
        });
    }

    /* ---------------- 3D tilt on cards (follows the cursor) ---------------- */
    if (finePointer && !reduceMotion) {
        const tiltSelector = '.product-card, .testimonial-card, .brand-card, .feature-item';
        document.addEventListener('mousemove', (e) => {
            const card = e.target.closest ? e.target.closest(tiltSelector) : null;
            if (!card) return;
            const r = card.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            const max = card.matches('.product-card') ? 6 : 4;
            card.style.transform = `perspective(900px) rotateX(${(-py * max).toFixed(2)}deg) rotateY(${(px * max).toFixed(2)}deg) translateY(-4px)`;
        }, { passive: true });
        document.addEventListener('mouseout', (e) => {
            const card = e.target.closest ? e.target.closest(tiltSelector) : null;
            if (card && !card.contains(e.relatedTarget)) card.style.transform = '';
        });
    }

    /* ---------------- Hero parallax ---------------- */
    const heroSlider = $('.hero-slider');
    if (heroSlider && finePointer && !reduceMotion) {
        heroSlider.addEventListener('mousemove', (e) => {
            const r = heroSlider.getBoundingClientRect();
            const x = ((e.clientX - r.left) / r.width - 0.5) * -18;
            const y = ((e.clientY - r.top) / r.height - 0.5) * -12;
            $$('.hero-slide > a', heroSlider).forEach((a) => { a.style.transform = `translate3d(${x}px, ${y}px, 0) scale(1.04)`; });
        });
        heroSlider.addEventListener('mouseleave', () => $$('.hero-slide > a', heroSlider).forEach((a) => { a.style.transform = ''; }));
    }

    /* ---------------- 3D showcase gallery ---------------- */
    const showcase = $('.showcase-slider');
    if (showcase && window.Swiper) {
        const wrapper = $('.swiper-wrapper', showcase);
        const originals = $$('.swiper-slide', wrapper);
        // Coverflow loops smoothly with plenty of slides; repeat short galleries.
        for (let i = 0; wrapper.children.length < 12 && originals.length; i++) {
            const clone = originals[i % originals.length].cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            wrapper.appendChild(clone);
        }
        const speed = parseInt(showcase.dataset.speed, 10) || 3200;
        new Swiper(showcase, {
            effect: 'coverflow',
            centeredSlides: true,
            slidesPerView: 'auto',
            loop: true,
            speed: 900,
            spaceBetween: 0,
            grabCursor: !finePointer,
            keyboard: { enabled: true },
            autoplay: reduceMotion ? false : { delay: speed, disableOnInteraction: false, pauseOnMouseEnter: true },
            coverflowEffect: { rotate: 24, stretch: -14, depth: 170, modifier: 1, slideShadows: true },
            navigation: { nextEl: $('.showcase-nav.next', showcase), prevEl: $('.showcase-nav.prev', showcase) },
            breakpoints: { 768: { coverflowEffect: { rotate: 28, stretch: 6, depth: 200, modifier: 1, slideShadows: true } } },
        });

        // The whole carousel leans gently toward the cursor.
        if (finePointer && !reduceMotion) {
            const section = showcase.closest('.showcase');
            section.addEventListener('mousemove', (e) => {
                const r = section.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                wrapper.parentElement.style.transform = `rotateY(${(x * 6).toFixed(2)}deg) rotateX(${(-y * 4).toFixed(2)}deg)`;
            });
            section.addEventListener('mouseleave', () => { wrapper.parentElement.style.transform = ''; });
            showcase.style.transition = 'transform .6s cubic-bezier(.22,1,.36,1)';
        }
    }

    /* ---------------- Product image zoom ---------------- */
    // Desktop: a selector frame on the photo + a large zoom window beside it (mouse wheel = zoom level).
    // Narrow screens / touch: the photo itself zooms in place where you press and drag.
    const mainImg = $('.pd-main-img');
    const lens = mainImg && $('.zoom-lens', mainImg);
    const zoomWin = $('.zoom-window');
    if (mainImg && lens && zoomWin) {
        const winImage = $('.zw-image', zoomWin);
        const levelEl = $('.zw-level b', zoomWin);
        const meter = $('.zw-meter span', zoomWin);
        const ZMIN = 1.5, ZMAX = 4, ZSTEP = 0.5;
        let zoom = 2.5;
        let activePane = null;
        let lastPoint = null;
        const sideMode = () => window.innerWidth > 991 && finePointer;
        const fmt = () => `${zoom % 1 ? zoom.toFixed(1) : zoom}×`;

        const showLevel = () => {
            levelEl.textContent = fmt();
            meter.style.width = `${((zoom - ZMIN) / (ZMAX - ZMIN)) * 100}%`;
        };

        const update = (pane, clientX, clientY) => {
            const img = $('img', pane);
            const r = img.getBoundingClientRect();
            const box = mainImg.getBoundingClientRect();
            const x = clientX - r.left;
            const y = clientY - r.top;
            if (x < 0 || y < 0 || x > r.width || y > r.height) { stop(); return; }
            activePane = pane;
            lastPoint = [clientX, clientY];

            if (sideMode()) {
                // Selector frame covers exactly the part shown in the zoom window.
                const lw = r.width / zoom;
                const lh = r.height / zoom;
                const lx = Math.max(0, Math.min(r.width - lw, x - lw / 2));
                const ly = Math.max(0, Math.min(r.height - lh, y - lh / 2));
                lens.style.width = `${lw}px`;
                lens.style.height = `${lh}px`;
                lens.style.transform = `translate3d(${r.left - box.left + lx}px, ${r.top - box.top + ly}px, 0)`;
                winImage.style.backgroundImage = `url("${img.currentSrc || img.src}")`;
                // The window is the same size as the photo, so the offset scales with the window/photo ratio.
                const ratio = winImage.clientWidth / r.width;
                winImage.style.backgroundSize = `${r.width * zoom * ratio}px ${r.height * zoom * ratio}px`;
                winImage.style.backgroundPosition = `${-lx * zoom * ratio}px ${-ly * zoom * ratio}px`;
                mainImg.classList.add('zoom-side');
                zoomWin.classList.add('show');
            } else {
                img.style.transformOrigin = `${(x / r.width) * 100}% ${(y / r.height) * 100}%`;
                img.style.transform = `scale(${zoom})`;
                pane.classList.add('zoom-inplace');
            }
            mainImg.classList.add('zoom-on');
        };

        const stop = () => {
            zoomWin.classList.remove('show');
            mainImg.classList.remove('zoom-on', 'zoom-side');
            $$('.zoom-pane', mainImg).forEach((p) => {
                p.classList.remove('zoom-inplace');
                const img = $('img', p);
                img.style.transform = '';
            });
            activePane = null;
        };

        $$('.zoom-pane', mainImg).forEach((pane) => {
            pane.addEventListener('mousemove', (e) => update(pane, e.clientX, e.clientY));
            pane.addEventListener('mouseleave', stop);
            // Mouse wheel changes the zoom level while hovering the photo.
            pane.addEventListener('wheel', (e) => {
                if (!activePane) return;
                e.preventDefault();
                zoom = Math.min(ZMAX, Math.max(ZMIN, zoom + (e.deltaY < 0 ? ZSTEP : -ZSTEP)));
                showLevel();
                if (lastPoint) update(pane, lastPoint[0], lastPoint[1]);
            }, { passive: false });

            // Touch: press briefly, then drag to pan the zoomed photo. A quick flick still swipes photos.
            let holdTimer = null;
            let touching = false;
            pane.addEventListener('touchstart', (e) => {
                const t = e.touches[0];
                holdTimer = setTimeout(() => { touching = true; update(pane, t.clientX, t.clientY); }, 160);
            }, { passive: true });
            pane.addEventListener('touchmove', (e) => {
                if (!touching) { clearTimeout(holdTimer); return; }
                e.preventDefault();
                const t = e.touches[0];
                update(pane, t.clientX, t.clientY);
            }, { passive: false });
            pane.addEventListener('touchend', () => { clearTimeout(holdTimer); touching = false; stop(); });
        });
        window.addEventListener('scroll', () => { if (activePane && !finePointer) stop(); }, { passive: true });
        showLevel();
    }

    /* ---------------- Sparkle trail behind the cursor ---------------- */
    // Small gold stars drift out from the cursor and fade. Drawn on one canvas, idle when the mouse rests.
    // Starts together with the custom cursor (first real mouse movement), so it works on every computer.
    if (!reduceMotion && cursorStyle !== 'off' && document.body.dataset.cursorTrail === '1') document.addEventListener('gh:cursor-on', () => {
        const canvas = document.createElement('canvas');
        canvas.className = 'cursor-trail';
        document.body.appendChild(canvas);
        const ctx = canvas.getContext('2d');
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        const resize = () => {
            canvas.width = window.innerWidth * dpr;
            canvas.height = window.innerHeight * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        };
        resize();
        window.addEventListener('resize', resize);

        const colors = ['#c9a24a', '#e9c977', '#f3dc9a', '#1f4d36'];
        const stars = [];
        let lastX = null, lastY = null, running = false;

        const drawStar = (x, y, r, rot, color, alpha) => {
            ctx.save();
            ctx.globalAlpha = alpha;
            ctx.translate(x, y);
            ctx.rotate(rot);
            ctx.fillStyle = color;
            ctx.beginPath();
            // Four-point sparkle
            for (let i = 0; i < 8; i++) {
                const rad = i % 2 === 0 ? r : r * 0.32;
                const a = (Math.PI / 4) * i;
                ctx.lineTo(Math.cos(a) * rad, Math.sin(a) * rad);
            }
            ctx.closePath();
            ctx.fill();
            ctx.restore();
        };

        // Warm "flame" glow used with the honey cursor: bright gold core fading through amber to transparent.
        // Normal blending (not additive) so it stays visible on the site's white and cream backgrounds.
        const drawGlow = (x, y, r, k) => {
            const g = ctx.createRadialGradient(x, y, 0, x, y, r);
            g.addColorStop(0, `rgba(255, 226, 120, ${0.62 * k})`);
            g.addColorStop(0.35, `rgba(247, 168, 38, ${0.45 * k})`);
            g.addColorStop(0.7, `rgba(222, 96, 18, ${0.18 * k})`);
            g.addColorStop(1, 'rgba(200, 60, 0, 0)');
            ctx.fillStyle = g;
            ctx.beginPath();
            ctx.arc(x, y, r, 0, Math.PI * 2);
            ctx.fill();
        };

        const tick = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = stars.length - 1; i >= 0; i--) {
                const s = stars[i];
                s.life -= 1;
                if (s.life <= 0) { stars.splice(i, 1); continue; }
                const k = s.life / s.max;
                if (s.glow) {
                    // Flames rise, wobble and shrink.
                    s.vy -= 0.03; s.vx *= 0.96;
                    s.x += s.vx + Math.sin((s.max - s.life) * 0.35 + s.seed) * 0.35; s.y += s.vy;
                    drawGlow(s.x, s.y, s.r * (0.35 + k * 0.65), k);
                } else {
                    s.x += s.vx; s.y += s.vy; s.vy += 0.035; s.rot += s.spin;
                    drawStar(s.x, s.y, s.r * (0.4 + k * 0.6), s.rot, s.color, k);
                }
            }
            if (stars.length) requestAnimationFrame(tick); else running = false;
        };

        const honey = cursorStyle !== 'leaf';
        const spawnGlow = (x, y, spread = 1) => {
            const max = 26 + Math.random() * 18;
            stars.push({
                glow: true, x: x + (Math.random() - 0.5) * 6 * spread, y: y + (Math.random() - 0.5) * 6 * spread,
                vx: (Math.random() - 0.5) * 0.8 * spread, vy: -0.3 - Math.random() * 0.6,
                r: 11 + Math.random() * 10, seed: Math.random() * 6, life: max, max,
            });
        };

        document.addEventListener('mousemove', (e) => {
            if (!html.classList.contains('has-cursor') || (e.target.closest && e.target.closest('.zoom-pane')) || html.classList.contains('c-text')) { lastX = e.clientX; lastY = e.clientY; return; }
            if (lastX !== null) {
                const dist = Math.hypot(e.clientX - lastX, e.clientY - lastY);
                if (honey) {
                    // Glow comes off the body of the arrow (just behind its tip); a few gold sparkles ride along.
                    const glows = Math.min(6, Math.floor(dist / 5));
                    for (let i = 0; i < glows && stars.length < 90; i++) {
                        const f = (i + 1) / (glows + 1);
                        spawnGlow(lastX + (e.clientX - lastX) * f + 7, lastY + (e.clientY - lastY) * f + 11);
                    }
                    if (glows && Math.random() < 0.35) {
                        const max = 30 + Math.random() * 16;
                        stars.push({ x: e.clientX + 8, y: e.clientY + 12, vx: (Math.random() - 0.5) * 1.4, vy: -0.6 - Math.random(),
                            r: 2.5 + Math.random() * 2.5, rot: Math.random() * Math.PI, spin: 0.1, color: colors[(Math.random() * 3) | 0], life: max, max });
                    }
                    if (glows === 0) return;
                    lastX = e.clientX; lastY = e.clientY;
                    if (!running) { running = true; requestAnimationFrame(tick); }
                    return;
                }
                // One sparkle every ~12px travelled keeps the trail even at any speed.
                const count = Math.min(3, Math.floor(dist / 12));
                for (let i = 0; i < count && stars.length < 70; i++) {
                    const max = 34 + Math.random() * 22;
                    stars.push({
                        x: e.clientX + (Math.random() - 0.5) * 8,
                        y: e.clientY + (Math.random() - 0.5) * 8,
                        vx: (Math.random() - 0.5) * 1.1,
                        vy: (Math.random() - 0.5) * 1.1 - 0.4,
                        r: 3 + Math.random() * 4.5,
                        rot: Math.random() * Math.PI,
                        spin: (Math.random() - 0.5) * 0.12,
                        color: colors[(Math.random() * colors.length) | 0],
                        life: max, max,
                    });
                }
                if (count === 0) return;
            }
            lastX = e.clientX; lastY = e.clientY;
            if (!running && stars.length) { running = true; requestAnimationFrame(tick); }
        }, { passive: true });

        // A little burst when clicking.
        document.addEventListener('mousedown', (e) => {
            if (honey) for (let i = 0; i < 8; i++) spawnGlow(e.clientX + 4, e.clientY + 6, 3);
            for (let i = 0; i < 10 && stars.length < 100; i++) {
                const a = (Math.PI * 2 * i) / 10;
                const max = 30 + Math.random() * 14;
                stars.push({ x: e.clientX, y: e.clientY, vx: Math.cos(a) * 2.4, vy: Math.sin(a) * 2.4, r: 3.5 + Math.random() * 3,
                    rot: 0, spin: 0.1, color: colors[i % 3], life: max, max });
            }
            if (!running) { running = true; requestAnimationFrame(tick); }
        });
    }, { once: true });

    /* ---------------- Click ripple on buttons ---------------- */
    if (!reduceMotion) {
        document.addEventListener('pointerdown', (e) => {
            const btn = e.target.closest('.btn, .open-quantity-btn, .header-search button, .showcase-follow');
            if (!btn) return;
            const r = btn.getBoundingClientRect();
            const size = Math.max(r.width, r.height) * 2;
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            Object.assign(ripple.style, { width: `${size}px`, height: `${size}px`, left: `${e.clientX - r.left - size / 2}px`, top: `${e.clientY - r.top - size / 2}px` });
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 650);
        });
    }

    /* ---------------- Drifting leaves in feature sections ---------------- */
    if (!reduceMotion) {
        const leafSvg = '<svg viewBox="0 0 24 24"><path d="M3 21C3 11 9 4 21 3c-1 12-8 18-18 18Z" fill="currentColor"/><path d="M4 20 14 10" stroke="rgba(255,255,255,.6)" stroke-width="1.2"/></svg>';
        $$('.showcase, .testimonial-section, .promo-banner').forEach((section) => {
            const layer = document.createElement('div');
            layer.className = 'leaf-layer';
            layer.setAttribute('aria-hidden', 'true');
            const count = section.matches('.promo-banner') ? 5 : 8;
            for (let i = 0; i < count; i++) {
                const leaf = document.createElement('span');
                leaf.className = 'drift-leaf';
                leaf.innerHTML = leafSvg;
                leaf.style.setProperty('--x', `${Math.round(Math.random() * 100)}%`);
                leaf.style.setProperty('--s', (0.6 + Math.random() * 0.9).toFixed(2));
                leaf.style.setProperty('--dur', `${14 + Math.random() * 12}s`);
                leaf.style.setProperty('--delay', `${-Math.random() * 20}s`);
                leaf.style.setProperty('--sway', `${30 + Math.random() * 60}px`);
                layer.appendChild(leaf);
            }
            section.prepend(layer);
        });
    }
})();
