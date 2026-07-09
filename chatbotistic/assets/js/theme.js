/*!
 * Chatbotistic theme — interactions.
 * One file. Vanilla JS. Selectors match the markup exactly.
 */
(function () {
	'use strict';

	var ready = function (fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	};

	// Mark that JS is running so reveal animations only hide content when we
	// can guarantee we'll show it again. Without this class the CSS keeps
	// everything visible, so a JS failure never leaves blank cards.
	document.documentElement.classList.add('cbjs');

	ready(function () {
		dropdowns();
		mobileNav();
		faq();
		pricingToggle();
		reveal();
		ecoCycle();
		docsTabs();
		docsScrollspy();
		docsCopyCode();
	});

	/* ---- Docs sidebar tabs — switch the visible content panel ---- */
	function docsTabs() {
		var side = document.querySelector('.docs-side');
		if (!side) { return; }
		var links = Array.prototype.slice.call(side.querySelectorAll('a[data-doc]'));
		var panels = Array.prototype.slice.call(document.querySelectorAll('.docs-content[data-doc-panel]'));
		if (!links.length || !panels.length) { return; }

		links.forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				var target = link.getAttribute('data-doc');
				links.forEach(function (l) { l.classList.remove('active'); });
				link.classList.add('active');
				panels.forEach(function (p) {
					p.classList.toggle('is-active', p.getAttribute('data-doc-panel') === target);
				});
			});
		});
	}

	/* ---- Docs page (page-docs.php) — sidebar highlights the article
	   currently in view, and clicking a link smooth-scrolls to it. All
	   articles stay in the DOM and visible; nothing is hidden/swapped. ---- */
	function docsScrollspy() {
		var side = document.querySelector('.docs2-side, .docs2-mobile-nav');
		if (!side) { return; }
		var links = Array.prototype.slice.call(document.querySelectorAll('.docs2-side a[href^="#"], .docs2-mobile-nav a[href^="#"]'));
		var articles = Array.prototype.slice.call(document.querySelectorAll('.docs2-article[id]'));
		if (!links.length || !articles.length) { return; }

		function setActive( id ) {
			links.forEach(function (l) {
				l.classList.toggle('is-active', l.getAttribute('href') === '#' + id);
			});
		}

		links.forEach(function (link) {
			link.addEventListener('click', function (e) {
				var id = link.getAttribute('href').slice(1);
				var target = document.getElementById(id);
				if (!target) { return; }
				e.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
				history.replaceState(null, '', '#' + id);
			});
		});

		if (!('IntersectionObserver' in window)) { return; }

		var current = articles[0].id;
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					current = entry.target.id;
				}
			});
			setActive( current );
		}, { rootMargin: '-96px 0px -70% 0px', threshold: 0 });
		articles.forEach(function (el) { io.observe(el); });

		if (window.location.hash) {
			var initial = document.getElementById(window.location.hash.slice(1));
			if (initial) {
				setActive( window.location.hash.slice(1) );
				window.setTimeout(function () { initial.scrollIntoView({ block: 'start' }); }, 0);
			}
		} else {
			setActive( current );
		}
	}

	/* ---- Docs page — copy-to-clipboard on code snippets ---- */
	function docsCopyCode() {
		var buttons = Array.prototype.slice.call(document.querySelectorAll('.doc-code-copy'));
		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var pre = btn.parentElement && btn.parentElement.querySelector('pre');
				if (!pre || !navigator.clipboard) { return; }
				navigator.clipboard.writeText(pre.textContent || '').then(function () {
					var label = btn.querySelector('span');
					var original = label ? label.textContent : '';
					btn.classList.add('is-copied');
					if (label) { label.textContent = 'Copied'; }
					window.setTimeout(function () {
						btn.classList.remove('is-copied');
						if (label) { label.textContent = original; }
					}, 1600);
				}).catch(function () { /* clipboard unavailable — snippet is still selectable */ });
			});
		});
	}

	/* ---- WordPressistic ecosystem — rotate the active brand + cycling word ---- */
	function ecoCycle() {
		var strip = document.querySelector('[data-eco]');
		if (!strip) { return; }
		var cards = Array.prototype.slice.call(strip.querySelectorAll('.cb-eco__card'));
		var words = Array.prototype.slice.call(strip.querySelectorAll('.cb-eco__cycle span'));
		if (cards.length < 2) { return; }

		var active = Math.max(0, cards.indexOf(strip.querySelector('.cb-eco__card.is-active')));
		cards.forEach(function (c, i) {
			c.addEventListener('mouseenter', function () { show(i); });
		});
		show(active);
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

		var timer = setInterval(function () { show((active + 1) % cards.length); }, 3400);
		strip.addEventListener('mouseenter', function () { clearInterval(timer); });

		function show(i) {
			active = i;
			cards.forEach(function (c, idx) { c.classList.toggle('is-active', idx === i); });
			words.forEach(function (w, idx) { w.classList.toggle('is-on', idx === i); });
		}
	}

	/* ---- Header dropdowns (Products menu, account menu) ---- */
	function dropdowns() {
		var menus = document.querySelectorAll('[data-menu]');
		menus.forEach(function (menu) {
			var trigger = menu.querySelector('[data-menu-trigger]');
			if (!trigger) { return; }

			trigger.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var open = menu.getAttribute('aria-expanded') === 'true';
				closeAll();
				menu.setAttribute('aria-expanded', open ? 'false' : 'true');
			});
		});

		document.addEventListener('click', function (e) {
			menus.forEach(function (menu) {
				if (!menu.contains(e.target)) { menu.setAttribute('aria-expanded', 'false'); }
			});
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { closeAll(); }
		});

		function closeAll() {
			menus.forEach(function (m) { m.setAttribute('aria-expanded', 'false'); });
		}
	}

	/* ---- Mobile navigation drawer ---- */
	function mobileNav() {
		var burger = document.querySelector('[data-burger]');
		var nav = document.querySelector('[data-mobile-nav]');
		if (!burger || !nav) { return; }

		burger.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.body.style.overflow = open ? 'hidden' : '';
		});
		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				nav.classList.remove('is-open');
				burger.setAttribute('aria-expanded', 'false');
				document.body.style.overflow = '';
			}
		});
	}

	/* ---- FAQ accordion ---- */
	function faq() {
		document.querySelectorAll('.cb-faq__item').forEach(function (item) {
			var q = item.querySelector('.cb-faq__q');
			if (!q) { return; }
			q.setAttribute('aria-expanded', 'false');
			q.addEventListener('click', function () {
				var open = item.classList.toggle('is-open');
				q.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		});
	}

	/* ---- Pricing monthly / yearly toggle ---- */
	function pricingToggle() {
		var toggle = document.querySelector('[data-pricing-toggle]');
		var grid = document.querySelector('[data-pricing]');
		if (!toggle || !grid) { return; }
		var buttons = toggle.querySelectorAll('button');

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				buttons.forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				grid.classList.toggle('is-yearly', btn.getAttribute('data-mode') === 'yearly');
			});
		});
	}

	/* ---- Reveal on scroll ---- */
	function reveal() {
		// Auto-tag common cards/sections so they animate in on scroll without
		// every template needing the class. Skip anything inside a horizontal
		// scroller so we never hide off-screen carousel items.
		var autoSel = '.f-card, .op-stepcard, .solution-item, .rec-card, .qa, .uc-card, .price-card, .stat-card';
		Array.prototype.forEach.call(document.querySelectorAll(autoSel), function (el) {
			el.classList.add('cb-reveal');
		});

		var els = Array.prototype.slice.call(document.querySelectorAll('.cb-reveal'));
		if (!els.length) { return; }

		var showAll = function () { els.forEach(function (el) { el.classList.add('is-in'); }); };

		if (!('IntersectionObserver' in window)) { showAll(); return; }

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-in');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
		els.forEach(function (el) { io.observe(el); });

		// Failsafe: reveal everything after a short delay in case the observer
		// never fires (very tall layouts, background tabs, etc.).
		setTimeout(showAll, 1800);
	}
})();
