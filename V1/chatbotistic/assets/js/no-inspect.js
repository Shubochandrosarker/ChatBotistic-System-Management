/*!
 * Chatbotistic — casual source-protection deterrent.
 *
 * Disables the context menu and common "view source / devtools" shortcuts.
 * This only discourages casual visitors: browser source and developer tools
 * cannot truly be blocked on the client side, so do not treat this as a
 * security control. Real protection belongs server-side.
 */
(function () {
	'use strict';

	document.addEventListener('contextmenu', function (e) { e.preventDefault(); });
	document.addEventListener('dragstart', function (e) {
		if (e.target && (e.target.tagName === 'IMG' || e.target.tagName === 'A')) { e.preventDefault(); }
	});

	document.addEventListener('keydown', function (e) {
		var key = (e.key || '').toUpperCase();
		var mod = e.ctrlKey || e.metaKey;
		var blocked =
			e.keyCode === 123 ||                       /* F12 */
			(mod && e.shiftKey && (key === 'I' || key === 'J' || key === 'C')) || /* devtools */
			(mod && (key === 'U' || key === 'S'));     /* view-source / save page */
		if (blocked) {
			e.preventDefault();
			e.stopPropagation();
		}
	});
})();
