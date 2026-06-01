/*!
 * Chatbotistic Connector — admin settings.
 */
(function () {
	'use strict';

	var cfg = window.CBCAdmin || {};
	var form = document.getElementById('cbc-admin-form');
	var notice = document.getElementById('cbc-admin-notice');
	if (!form || !cfg.ajax) { return; }

	function say(msg, ok) {
		notice.textContent = msg;
		notice.className = 'cbc-admin__notice ' + (ok ? 'is-ok' : 'is-err');
		notice.hidden = false;
	}

	function post(action, body) {
		var data = new FormData();
		data.append('action', action);
		data.append('nonce', cfg.nonce);
		Object.keys(body || {}).forEach(function (k) { data.append(k, body[k]); });
		return fetch(cfg.ajax, { method: 'POST', body: data, credentials: 'same-origin' })
			.then(function (r) { return r.json(); });
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var btn = document.getElementById('cbc-save');
		btn.disabled = true;

		// FormData carries plan_limits[ID][widgets] etc. natively.
		var data = new FormData(form);
		data.append('action', 'cbc_admin_save');
		data.append('nonce', cfg.nonce);

		fetch(cfg.ajax, { method: 'POST', body: data, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				btn.disabled = false;
				say((res.data && res.data.message) || 'Done.', !!res.success);
				if (res.success) { document.getElementById('cbc-pass').value = ''; }
			})
			.catch(function () { btn.disabled = false; say('Network error.', false); });
	});

	document.getElementById('cbc-test').addEventListener('click', function () {
		var btn = this;
		btn.disabled = true;
		say('Testing…', true);
		post('cbc_admin_test', {}).then(function (res) {
			btn.disabled = false;
			say((res.data && res.data.message) || 'Done.', !!res.success);
		}).catch(function () { btn.disabled = false; say('Network error.', false); });
	});
})();
