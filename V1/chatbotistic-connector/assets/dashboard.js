/*!
 * Chatbotistic Connector — member dashboard.
 * Vanilla JS. All data flows through admin-ajax (see class-ajax.php).
 */
(function () {
	'use strict';

	var CFG = window.CBC || {};
	var app = document.getElementById('cbc-app');
	if (!app || !CFG.ajax) { return; }

	var state = { widgets: [], membership: {}, usage: {} };

	/* ---- helpers ---- */
	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	function el(id) { return document.getElementById(id); }

	function api(action, data) {
		var body = new FormData();
		body.append('action', action);
		body.append('nonce', CFG.nonce);
		Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); });
		return fetch(CFG.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) {
				// Read as text first — PHP notices/warnings get prepended
				// to the JSON body by some plugins, breaking r.json().
				// Strip leading garbage up to the first { or [.
				return r.text().then(function (txt) {
					var s = String(txt || '').trim();
					var i = s.search(/[\{\[]/);
					if (i > 0) { s = s.slice(i); }
					if (!s) {
						throw new Error('Empty response from server (HTTP ' + r.status + ').');
					}
					try {
						return JSON.parse(s);
					} catch (e) {
						var snippet = s.slice(0, 140).replace(/\s+/g, ' ');
						throw new Error('Server returned an invalid response. ' + snippet);
					}
				});
			})
			.then(function (res) {
				if (!res || res.success === undefined) {
					// Upstream returned a bare value (e.g. admin-ajax "0" for
					// an unregistered action, or a raw array).
					throw new Error('Unexpected response shape. Please reload and try again.');
				}
				if (!res.success) {
					var msg = (res && res.data && res.data.message) || 'Something went wrong.';
					throw new Error(msg);
				}
				return res.data;
			});
	}

	var noticeTimer;
	function notice(msg, ok) {
		var n = el('cbc-notice');
		n.textContent = msg;
		n.className = 'cbc-notice ' + (ok ? 'is-ok' : 'is-err');
		n.hidden = false;
		clearTimeout(noticeTimer);
		noticeTimer = setTimeout(function () { n.hidden = true; }, 6000);
	}

	/* ---- modal ---- */
	function openModal(title, html) {
		el('cbc-modal-body').innerHTML = '<h3>' + esc(title) + '</h3>' + html;
		el('cbc-modal').hidden = false;
	}
	function closeModal() { el('cbc-modal').hidden = true; el('cbc-modal-body').innerHTML = ''; }
	el('cbc-modal-x').addEventListener('click', closeModal);
	el('cbc-modal').addEventListener('click', function (e) { if (e.target === this) { closeModal(); } });

	/* ---- tabs ---- */
	var VALID_TABS = ['widgets', 'analytics', 'leads'];

	function activateTab(name) {
		if (VALID_TABS.indexOf(name) === -1) { return; }
		app.querySelectorAll('.cbc-tab').forEach(function (t) {
			var on = (t.getAttribute('data-tab') === name);
			t.classList.toggle('is-active', on);
			t.setAttribute('aria-selected', on ? 'true' : 'false');
		});
		app.querySelectorAll('.cbc-panel').forEach(function (p) {
			var on = (p.id === 'cbc-panel-' + name);
			p.hidden = !on;
			p.classList.toggle('is-active', on);
		});
		if (name === 'analytics') { loadAnalytics(); }
		if (name === 'leads')     { loadLeads(); }
	}

	app.querySelectorAll('.cbc-tab').forEach(function (tab) {
		tab.addEventListener('click', function () {
			activateTab(tab.getAttribute('data-tab'));
		});
	});

	// Honour the shortcode's default_tab attribute on first load. PHP has
	// already set the matching .is-active class and hidden state, but we
	// still need to fire the loader for analytics/leads (their AJAX
	// fetchers only run when a tab activates).
	var initial = app.getAttribute('data-default-tab') || 'widgets';
	if (initial !== 'widgets' && VALID_TABS.indexOf(initial) !== -1) {
		// Defer until loadDashboard() has resolved membership/usage so
		// the loaders have CFG.nonce + auth context in place.
		document.addEventListener('cbc:ready', function () {
			activateTab(initial);
		}, { once: true });
	}

	/* ============ Dashboard ============ */
	function loadDashboard() {
		api('cbc_dashboard', {}).then(function (d) {
			state = d;
			el('cbc-plan').textContent = d.membership.active
				? (d.membership.plan || 'Active plan')
				: 'No active plan';
			el('cbc-usage').textContent = d.membership.active
				? (d.usage.widgets + (limitLabel(d.membership.widget_limit)) + ' widgets · '
					+ d.usage.agents + limitLabel(d.membership.agent_limit) + ' agents')
				: '';
			renderWidgets();
			document.dispatchEvent(new CustomEvent('cbc:ready'));
		}).catch(function (e) {
			el('cbc-widgets').innerHTML = '<div class="cbc-empty"><strong>Could not load</strong>' + esc(e.message) + '</div>';
			document.dispatchEvent(new CustomEvent('cbc:ready'));
		});
	}
	function limitLabel(limit) { return limit === -1 ? '' : ('/' + limit); }

	function renderWidgets() {
		var box = el('cbc-widgets');
		if (!state.membership.active) {
			box.innerHTML = '<div class="cbc-empty"><strong>Your plan is not active</strong>'
				+ 'Activate a plan to create widgets. <a href="' + esc(CFG.plans) + '">See plans</a></div>';
			return;
		}
		if (!state.widgets.length) {
			box.innerHTML = '<div class="cbc-empty"><strong>No widgets yet</strong>Create your first WhatsApp widget to get started.</div>';
			return;
		}
		box.innerHTML = state.widgets.map(widgetCard).join('');
	}

	function widgetCard(w) {
		var agents = (w.operators || []).map(agentRow).join('') ||
			'<p class="cbc-widget__meta">No agents yet. Add a WhatsApp number so this widget can receive chats.</p>';
		return '<div class="cbc-widget" data-widget="' + esc(w.id) + '">'
			+ '<div class="cbc-widget__head">'
			+   '<span class="cbc-widget__dot" style="background:' + esc(w.color || '#25D366') + '"></span>'
			+   '<span class="cbc-widget__name">' + esc(w.name)
			+     '<span class="cbc-widget__meta"> · ' + (w.operators || []).length + ' agent(s)</span></span>'
			+   '<label class="cbc-switch"><input type="checkbox" data-action="toggle"' + (w.active ? ' checked' : '') + '><span></span></label>'
			+   '<div class="cbc-widget__actions">'
			+     '<button class="cbc-btn cbc-btn--sm" data-action="expand">Manage</button>'
			+     '<button class="cbc-btn cbc-btn--sm" data-action="customize">Customize</button>'
			+     '<button class="cbc-btn cbc-btn--sm" data-action="embed">Embed</button>'
			+     '<button class="cbc-btn cbc-btn--sm cbc-btn--danger" data-action="delete">Delete</button>'
			+   '</div>'
			+ '</div>'
			+ '<div class="cbc-widget__body">'
			+   '<div class="cbc-widget__section-title">WhatsApp agents</div>'
			+   '<div class="cbc-agents">' + agents + '</div>'
			+   '<button class="cbc-btn cbc-btn--sm cbc-btn--primary" data-action="add-agent" style="margin-top:6px;">+ Add agent</button>'
			+ '</div>'
			+ '</div>';
	}

	function agentRow(a) {
		return '<div class="cbc-agent" data-agent="' + esc(a.id) + '">'
			+ '<div class="cbc-agent__info"><b>' + esc(a.name) + '</b>'
			+   '<span>' + esc(a.number) + (a.post ? ' · ' + esc(a.post) : '') + '</span></div>'
			+ '<button class="cbc-btn cbc-btn--sm" data-action="faq">FAQs</button>'
			+ '<button class="cbc-btn cbc-btn--sm" data-action="edit-agent">Edit</button>'
			+ '<button class="cbc-btn cbc-btn--sm cbc-btn--danger" data-action="del-agent">Remove</button>'
			+ '</div>';
	}

	/* ============ Widget forms ============ */
	function widgetForm(w) {
		w = w || {};
		return '<div class="cbc-field"><label>Widget name</label>'
			+ '<input class="cbc-input" id="f-name" value="' + esc(w.name || '') + '" maxlength="100"></div>'
			+ '<div class="cbc-row">'
			+   '<div class="cbc-field"><label>Button colour</label><input type="color" class="cbc-input" id="f-color" value="' + esc(w.color || '#25D366') + '"></div>'
			+   '<div class="cbc-field"><label>Position</label><select id="f-position"><option value="right">Bottom right</option><option value="left">Bottom left</option></select></div>'
			+ '</div>'
			+ '<div class="cbc-field"><label>Greeting message</label>'
			+ '<input class="cbc-input" id="f-widget_message" value="' + esc(w.widgetMessage || '') + '" placeholder="Hi! How can we help?"></div>'
			+ '<div class="cbc-field"><label>Button text</label>'
			+ '<input class="cbc-input" id="f-button_message" value="' + esc(w.buttonMessage || '') + '" placeholder="Chat with us"></div>'
			+ '<div class="cbc-field"><label>Landing headline</label>'
			+ '<input class="cbc-input" id="f-legend" value="' + esc(w.legend || '') + '"></div>'
			+ '<div class="cbc-field"><label>Offline message</label>'
			+ '<textarea id="f-offline_message">' + esc(w.offlineMessage || '') + '</textarea></div>'
			+ '<div class="cbc-row">'
			+   '<div class="cbc-field"><label>Landing primary</label><input type="color" class="cbc-input" id="f-landing_primary" value="' + esc(w.landingPrimaryColor || '#0a0e1a') + '"></div>'
			+   '<div class="cbc-field"><label>Landing secondary</label><input type="color" class="cbc-input" id="f-landing_secondary" value="' + esc(w.landingSecondaryColor || '#4f8bff') + '"></div>'
			+ '</div>'
			+ '<label style="display:flex;gap:8px;align-items:center;font-size:14px;color:var(--cbc-soft);">'
			+ '<input type="checkbox" id="f-auto_open"' + (w.isopen ? ' checked' : '') + '> Auto-open the chat bubble</label>';
	}

	function collectWidget() {
		var d = {
			name: el('f-name').value.trim(),
			color: el('f-color').value,
			position: el('f-position').value,
			widget_message: el('f-widget_message').value,
			button_message: el('f-button_message').value,
			legend: el('f-legend').value,
			offline_message: el('f-offline_message').value,
			landing_primary: el('f-landing_primary').value,
			landing_secondary: el('f-landing_secondary').value,
			auto_open: el('f-auto_open').checked ? 'true' : 'false'
		};
		return d;
	}

	function openWidgetCreate() {
		openModal('New widget', widgetForm({})
			+ '<div class="cbc-form-actions">'
			+ '<button class="cbc-btn cbc-btn--primary" id="cbc-save">Create widget</button>'
			+ '<button class="cbc-btn cbc-btn--ghost" id="cbc-cancel">Cancel</button></div>');
		el('cbc-cancel').addEventListener('click', closeModal);
		el('cbc-save').addEventListener('click', function () {
			var d = collectWidget();
			if (!d.name) { notice('Enter a widget name.', false); return; }
			this.disabled = true;
			api('cbc_widget_create', d).then(function (r) {
				closeModal(); notice(r.message, true); loadDashboard();
			}).catch(function (e) { notice(e.message, false); el('cbc-save').disabled = false; });
		});
	}

	function openWidgetCustomize(widget) {
		openModal('Customize widget', '<div class="cbc-loading">Loading…</div>');
		api('cbc_widget_get', { widget_id: widget.id }).then(function (d) {
			el('cbc-modal-body').innerHTML = '<h3>Customize widget</h3>' + widgetForm(d.widget)
				+ '<div class="cbc-form-actions">'
				+ '<button class="cbc-btn cbc-btn--primary" id="cbc-save">Save changes</button>'
				+ '<button class="cbc-btn cbc-btn--ghost" id="cbc-cancel">Cancel</button></div>';
			el('f-position').value = (d.widget.rightpos === false) ? 'left' : 'right';
			el('cbc-cancel').addEventListener('click', closeModal);
			el('cbc-save').addEventListener('click', function () {
				var payload = collectWidget();
				payload.widget_id = widget.id;
				this.disabled = true;
				api('cbc_widget_update', payload).then(function (r) {
					closeModal(); notice(r.message, true); loadDashboard();
				}).catch(function (e) { notice(e.message, false); el('cbc-save').disabled = false; });
			});
		}).catch(function (e) {
			el('cbc-modal-body').innerHTML = '<h3>Customize widget</h3><p>' + esc(e.message) + '</p>';
		});
	}

	/* ============ Agent form ============ */
	function agentForm(a) {
		a = a || {};
		var fields = (a.form || []).map(formFieldRow);
		return '<div class="cbc-field"><label>Agent name</label>'
			+ '<input class="cbc-input" id="a-name" value="' + esc(a.name || '') + '"></div>'
			+ '<div class="cbc-field"><label>WhatsApp number (with country code)</label>'
			+ '<input class="cbc-input" id="a-number" value="' + esc(a.number || '') + '" placeholder="+447911123456"></div>'
			+ '<div class="cbc-field"><label>Role / department</label>'
			+ '<input class="cbc-input" id="a-post" value="' + esc(a.post || '') + '" placeholder="Sales"></div>'
			+ '<div class="cbc-field"><label>Intro message</label>'
			+ '<textarea id="a-message">' + esc(a.message || '') + '</textarea></div>'
			+ '<div class="cbc-widget__section-title">Lead-capture form</div>'
			+ '<div id="a-fields">' + (fields.join('') || '') + '</div>'
			+ '<button class="cbc-btn cbc-btn--sm" id="a-add-field" type="button">+ Add field</button>';
	}
	function formFieldRow(f) {
		f = f || {};
		var types = ['text', 'email', 'tel', 'url', 'number', 'checkbox'];
		var opts = types.map(function (t) {
			return '<option value="' + t + '"' + (f.type === t ? ' selected' : '') + '>' + t + '</option>';
		}).join('');
		return '<div class="cbc-repeat__row">'
			+ '<input class="cbc-input cbc-ff-label" placeholder="Field label" value="' + esc(f.label || '') + '">'
			+ '<select class="cbc-ff-type">' + opts + '</select>'
			+ '<button class="cbc-btn cbc-btn--sm cbc-btn--danger" type="button" data-action="rm-row">×</button>'
			+ '</div>';
	}
	function collectAgentFields() {
		var rows = [];
		document.querySelectorAll('#a-fields .cbc-repeat__row').forEach(function (row) {
			var label = row.querySelector('.cbc-ff-label').value.trim();
			if (label) { rows.push({ label: label, type: row.querySelector('.cbc-ff-type').value, required: false }); }
		});
		return rows;
	}

	function openAgentForm(widgetId, agent) {
		var isEdit = !!agent;
		openModal(isEdit ? 'Edit agent' : 'Add WhatsApp agent', agentForm(agent)
			+ '<div class="cbc-form-actions">'
			+ '<button class="cbc-btn cbc-btn--primary" id="cbc-save">' + (isEdit ? 'Save agent' : 'Add agent') + '</button>'
			+ '<button class="cbc-btn cbc-btn--ghost" id="cbc-cancel">Cancel</button></div>');
		el('cbc-cancel').addEventListener('click', closeModal);
		el('a-add-field').addEventListener('click', function () {
			el('a-fields').insertAdjacentHTML('beforeend', formFieldRow({}));
		});
		el('cbc-modal-body').addEventListener('click', function (e) {
			var b = e.target.closest('[data-action="rm-row"]');
			if (b) { b.closest('.cbc-repeat__row').remove(); }
		});
		el('cbc-save').addEventListener('click', function () {
			var d = {
				widget_id: widgetId,
				name: el('a-name').value.trim(),
				number: el('a-number').value.trim(),
				post: el('a-post').value,
				message: el('a-message').value,
				form_fields: JSON.stringify(collectAgentFields())
			};
			if (isEdit) { d.operator_id = agent.id; }
			this.disabled = true;
			api(isEdit ? 'cbc_operator_update' : 'cbc_operator_create', d).then(function (r) {
				closeModal(); notice(r.message, true); loadDashboard();
			}).catch(function (e) { notice(e.message, false); el('cbc-save').disabled = false; });
		});
	}

	/* ============ FAQ manager ============ */
	function openFaqManager(operatorId) {
		openModal('FAQ answers', '<div class="cbc-loading">Loading…</div>');
		api('cbc_faq_list', { operator_id: operatorId }).then(function (d) {
			var group = (d.groups && d.groups[0]) || null;
			var faqs = group ? (group.faqs || []) : [];
			var rows = faqs.map(faqRow).join('') || faqRow({});
			el('cbc-modal-body').innerHTML = '<h3>FAQ answers</h3>'
				+ '<p style="color:var(--cbc-soft);font-size:13px;">Questions shown inside the widget chat.</p>'
				+ '<div id="faq-rows">' + rows + '</div>'
				+ '<button class="cbc-btn cbc-btn--sm" id="faq-add" type="button">+ Add question</button>'
				+ '<div class="cbc-form-actions">'
				+ '<button class="cbc-btn cbc-btn--primary" id="cbc-save">Save FAQs</button>'
				+ '<button class="cbc-btn cbc-btn--ghost" id="cbc-cancel">Cancel</button></div>';
			el('cbc-cancel').addEventListener('click', closeModal);
			el('faq-add').addEventListener('click', function () {
				el('faq-rows').insertAdjacentHTML('beforeend', faqRow({}));
			});
			el('cbc-modal-body').addEventListener('click', function (e) {
				var b = e.target.closest('[data-action="rm-row"]');
				if (b) { b.closest('.cbc-repeat__row').remove(); }
			});
			el('cbc-save').addEventListener('click', function () {
				var faqs = [];
				document.querySelectorAll('#faq-rows .cbc-repeat__row').forEach(function (row) {
					var q = row.querySelector('.faq-q').value.trim();
					var a = row.querySelector('.faq-a').value.trim();
					if (q && a) { faqs.push({ question: q, answer: a }); }
				});
				if (!faqs.length) { notice('Add at least one question and answer.', false); return; }
				this.disabled = true;
				var payload = { operator_id: operatorId, faqs: JSON.stringify(faqs) };
				if (group) { payload.group_id = String(group.id); }
				api('cbc_faq_save', payload).then(function (r) {
					closeModal(); notice(r.message, true);
				}).catch(function (e) { notice(e.message, false); el('cbc-save').disabled = false; });
			});
		}).catch(function (e) {
			el('cbc-modal-body').innerHTML = '<h3>FAQ answers</h3><p>' + esc(e.message) + '</p>';
		});
	}
	function faqRow(f) {
		f = f || {};
		return '<div class="cbc-repeat__row" style="grid-template-columns:1fr;">'
			+ '<input class="cbc-input faq-q" placeholder="Question" value="' + esc(f.question || '') + '">'
			+ '<textarea class="faq-a" placeholder="Answer">' + esc(f.answer || '') + '</textarea>'
			+ '<button class="cbc-btn cbc-btn--sm cbc-btn--danger" type="button" data-action="rm-row" style="justify-self:start;">Remove</button>'
			+ '</div>';
	}

	/* ============ Analytics ============ */
	function loadAnalytics() {
		var box = el('cbc-analytics');
		box.innerHTML = '<div class="cbc-loading">Loading analytics…</div>';
		api('cbc_analytics', {}).then(function (d) {
			var t = d.totals;
			var max = Math.max(1, ...d.series.map(function (p) { return p.visits; }));
			var bars = d.series.map(function (p) {
				return '<div class="cbc-chart__bar" title="' + esc(p.date) + ': ' + p.leads + ' leads">'
					+ '<div class="cbc-chart__fill" style="height:' + Math.round(p.leads / max * 100) + '%"></div>'
					+ '<div class="cbc-chart__fill cbc-chart__fill--clicks" style="height:' + Math.round(p.visits / max * 100) + '%"></div>'
					+ '</div>';
			}).join('');
			box.innerHTML = '<div class="cbc-kpis">'
				+ kpi('Widgets', d.widgets) + kpi('Visits', t.visits)
				+ kpi('Clicks', t.clicks) + kpi('Leads', t.leads, true) + '</div>'
				+ '<div class="cbc-chart">' + (bars || '<div class="cbc-loading">No data in this range.</div>') + '</div>';
		}).catch(function (e) {
			box.innerHTML = '<div class="cbc-empty"><strong>Could not load analytics</strong>' + esc(e.message) + '</div>';
		});
	}
	function kpi(label, value, grad) {
		return '<div class="cbc-kpi"><div class="cbc-kpi__label">' + esc(label) + '</div>'
			+ '<div class="cbc-kpi__value' + (grad ? ' is-grad' : '') + '">' + esc(value) + '</div></div>';
	}

	/* ============ Leads ============ */
	function loadLeads() {
		var box = el('cbc-leads');
		box.innerHTML = '<div class="cbc-loading">Loading leads…</div>';
		api('cbc_leads', {}).then(function (d) {
			if (!d.leads.length) {
				box.innerHTML = '<div class="cbc-empty"><strong>No leads yet</strong>Leads appear here as visitors use your widgets.</div>';
				return;
			}
			var rows = d.leads.map(function (l) {
				var name = l.fields && (l.fields.Name || l.fields.Nombre || l.fields.name) || '—';
				return '<tr>'
					+ '<td data-label="Name">' + esc(name) + '</td>'
					+ '<td data-label="Phone">' + esc(l.phone || '—') + '</td>'
					+ '<td data-label="Widget">' + esc(l.widget || '—') + '</td>'
					+ '<td data-label="Country">' + esc(l.country || '—') + '</td>'
					+ '<td data-label="Date">' + esc(l.created || '') + '</td>'
					+ '<td data-label="Type"><span class="cbc-tag ' + (l.is_new ? 'cbc-tag--new">New' : 'cbc-tag--return">Return') + '</span></td>'
					+ '</tr>';
			}).join('');
			box.innerHTML = '<table class="cbc-table"><thead><tr>'
				+ '<th>Name</th><th>Phone</th><th>Widget</th><th>Country</th><th>Date</th><th>Type</th>'
				+ '</tr></thead><tbody>' + rows + '</tbody></table>';
		}).catch(function (e) {
			box.innerHTML = '<div class="cbc-empty"><strong>Could not load leads</strong>' + esc(e.message) + '</div>';
		});
	}

	/* ============ Event delegation ============ */
	el('cbc-new-widget').addEventListener('click', openWidgetCreate);

	el('cbc-widgets').addEventListener('click', function (e) {
		var btn = e.target.closest('[data-action]');
		if (!btn) { return; }
		var action = btn.getAttribute('data-action');
		var widgetEl = btn.closest('.cbc-widget');
		var widget = state.widgets.find(function (w) { return w.id === widgetEl.getAttribute('data-widget'); });
		if (!widget) { return; }

		if (action === 'expand') { widgetEl.classList.toggle('is-open'); return; }
		if (action === 'customize') { openWidgetCustomize(widget); return; }
		if (action === 'add-agent') { openAgentForm(widget.id, null); return; }
		if (action === 'embed') {
			openModal('Embed code', '<p style="color:var(--cbc-soft);font-size:13px;">Paste this before &lt;/body&gt; on your site.</p>'
				+ '<textarea readonly style="font-family:var(--cbc-mono);font-size:12px;" rows="3">' + esc(widget.embed) + '</textarea>'
				+ '<div class="cbc-form-actions"><button class="cbc-btn cbc-btn--primary" id="cbc-cancel">Done</button></div>');
			el('cbc-cancel').addEventListener('click', closeModal);
			return;
		}
		if (action === 'delete') {
			if (!window.confirm('Delete this widget? This cannot be undone.')) { return; }
			api('cbc_widget_delete', { widget_id: widget.id }).then(function (r) {
				notice(r.message, true); loadDashboard();
			}).catch(function (e2) { notice(e2.message, false); });
			return;
		}
		if (action === 'toggle') {
			api('cbc_widget_toggle', { widget_id: widget.id, active: btn.checked ? 'true' : 'false' })
				.then(function (r) { notice(r.message, true); })
				.catch(function (e2) { notice(e2.message, false); btn.checked = !btn.checked; });
			return;
		}

		// Agent-level actions.
		var agentEl = btn.closest('.cbc-agent');
		if (!agentEl) { return; }
		var agent = (widget.operators || []).find(function (a) { return a.id === agentEl.getAttribute('data-agent'); });
		if (!agent) { return; }
		if (action === 'faq') { openFaqManager(agent.id); }
		if (action === 'edit-agent') { openAgentForm(widget.id, agent); }
		if (action === 'del-agent') {
			if (!window.confirm('Remove this agent?')) { return; }
			api('cbc_operator_delete', { operator_id: agent.id }).then(function (r) {
				notice(r.message, true); loadDashboard();
			}).catch(function (e2) { notice(e2.message, false); });
		}
	});

	loadDashboard();
})();
