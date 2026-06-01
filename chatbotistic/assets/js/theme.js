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

	ready(function () {
		dropdowns();
		mobileNav();
		faq();
		pricingToggle();
		reveal();
		ecoCycle();
	});

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
		var els = document.querySelectorAll('.cb-reveal');
		if (!els.length) { return; }
		if (!('IntersectionObserver' in window)) {
			els.forEach(function (el) { el.classList.add('is-in'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-in');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
		els.forEach(function (el) { io.observe(el); });
	}
})();
