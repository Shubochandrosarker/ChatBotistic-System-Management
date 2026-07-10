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
		cbThemeToggle();
		dropdowns();
		mobileNav();
		faq();
		pricingToggle();
		reveal();
		ecoCycle();
		docsTabs();
	});

	/* ---- Light/dark theme toggle ----
	 * The <head> inline script (header.php) already set data-theme on
	 * <html> synchronously before first paint (no flash of wrong theme).
	 * This just wires the header button to flip + persist that choice.
	 */
	function cbThemeToggle() {
		var STORAGE_KEY = 'cb-theme';
		var root = document.documentElement;
		var btn = document.querySelector('[data-theme-toggle]');

		function currentTheme() {
			var attr = root.getAttribute('data-theme');
			if (attr === 'light' || attr === 'dark') { return attr; }
			var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			return prefersDark ? 'dark' : 'light';
		}

		function applyTheme(theme, persist) {
			root.setAttribute('data-theme', theme);
			if (btn) { btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false'); }
			var metaColor = document.querySelector('meta[data-theme-color]');
			if (metaColor) { metaColor.setAttribute('content', theme === 'dark' ? '#04060c' : '#f8f9fd'); }
			if (persist) {
				try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) { /* storage unavailable — theme still applies for this load */ }
			}
		}

		// Sync the button's a11y state with whatever the inline head
		// script already applied (it doesn't know about the button).
		applyTheme(currentTheme(), false);

		if (!btn) { return; }

		btn.addEventListener('click', function () {
			applyTheme(currentTheme() === 'dark' ? 'light' : 'dark', true);
		});

		// If the user hasn't made an explicit choice yet, keep following
		// the OS preference live (matches the prefers-color-scheme CSS
		// fallback). Once they click the toggle, localStorage takes over
		// and this listener naturally stops mattering for this tab.
		if (window.matchMedia) {
			var mq = window.matchMedia('(prefers-color-scheme: dark)');
			var onSchemeChange = function (e) {
				var stored = null;
				try { stored = localStorage.getItem(STORAGE_KEY); } catch (err) { /* ignore */ }
				if (stored === 'light' || stored === 'dark') { return; }
				applyTheme(e.matches ? 'dark' : 'light', false);
			};
			if (mq.addEventListener) { mq.addEventListener('change', onSchemeChange); }
			else if (mq.addListener) { mq.addListener(onSchemeChange); }
		}
	}

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
