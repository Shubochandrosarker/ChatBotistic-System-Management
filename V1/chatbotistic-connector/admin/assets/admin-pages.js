/**
 * Connector admin pages — Members / Payments / Licenses.
 * Vanilla JS. Reads global CBCAdmin = { ajax, nonce, plans:[] }.
 */
(function () {
	'use strict';

	const CBC = window.CBCAdmin || {};
	const $   = (sel, root = document) => root.querySelector(sel);
	const $$  = (sel, root = document) => Array.from(root.querySelectorAll(sel));

	function api(action, data = {}) {
		const body = new FormData();
		body.append('action', action);
		body.append('nonce', CBC.nonce);
		Object.entries(data).forEach(([k, v]) => {
			if (Array.isArray(v)) v.forEach(x => body.append(k + '[]', x));
			else body.append(k, v);
		});
		return fetch(CBC.ajax, { method: 'POST', body, credentials: 'same-origin' }).then(r => r.json());
	}

	function notice(boxId, msg, isError) {
		const box = $('#' + boxId);
		if (!box) return alert(msg);
		box.textContent = msg;
		box.className = 'cbc-notice' + (isError ? ' is-error' : '');
		box.hidden = false;
		setTimeout(() => { box.hidden = true; }, 5000);
	}

	function escHtml(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])); }

	function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

	function statusPill(s) {
		const tone = ({ active:'good', trial:'good', comped:'good', pending:'warn', past_due:'bad', expired:'bad', cancelled:'bad', paused:'warn', suspended:'warn', revoked:'bad', failed:'bad', completed:'good', refunded:'warn' })[s] || 'neutral';
		return `<span class="cbc-pill cbc-pill--${tone}">${escHtml((s || '').toUpperCase())}</span>`;
	}

	function pager(page, pages, onGo) {
		if (pages < 2) return '';
		const prev = `<button ${page<=1?'disabled':''} data-go="${page-1}">‹ Prev</button>`;
		const next = `<button ${page>=pages?'disabled':''} data-go="${page+1}">Next ›</button>`;
		return `<span style="color:#6b7280;">Page ${page} of ${pages}</span> ${prev} ${next}`;
	}

	function wirePager(pagerEl, onGo) {
		pagerEl.querySelectorAll('button[data-go]').forEach(b => b.addEventListener('click', () => onGo(parseInt(b.dataset.go, 10))));
	}

	// ── Members page ─────────────────────────────────────────────────────────
	function initMembers() {
		const tbody = $('#cbc-mb-table tbody');
		if (!tbody) return;
		const state = { page: 1, selected: new Set() };

		function selectionChanged() {
			const bulk = $('#cbc-mb-bulk');
			const cnt  = $('#cbc-mb-bulk-count');
			bulk.hidden = state.selected.size === 0;
			cnt.textContent = state.selected.size + ' selected';
		}

		function load() {
			tbody.innerHTML = `<tr><td colspan="8" class="cbc-loading">Loading…</td></tr>`;
			api('cbc_admin_members_list', {
				page:    state.page,
				search:  $('#cbc-mb-search').value,
				status:  $('#cbc-mb-status').value,
				plan_id: $('#cbc-mb-plan').value,
				per_page: 25,
			}).then(res => {
				if (!res.success) {
					tbody.innerHTML = `<tr><td colspan="8" class="cbc-loading">${escHtml(res.data?.message || 'Error')}</td></tr>`;
					return;
				}
				const rows = res.data.rows || [];
				if (!rows.length) {
					tbody.innerHTML = `<tr><td colspan="8" class="cbc-loading">No members match.</td></tr>`;
				} else {
					tbody.innerHTML = rows.map(r => `
						<tr data-id="${r.id}">
							<td><input type="checkbox" class="cbc-mb-row" value="${r.id}" ${state.selected.has(r.id)?'checked':''} /></td>
							<td><code>${escHtml(r.uuid)}</code></td>
							<td><strong>${escHtml(r.full_name || '—')}</strong><br><small style="color:#6b7280;">${escHtml(r.email || '')}</small></td>
							<td>${escHtml(r.plan_name || '—')}</td>
							<td>${statusPill(r.status)}</td>
							<td>${escHtml(r.billing_cycle || '—')}</td>
							<td>${escHtml((r.renewal_date || '').slice(0,10) || '—')}</td>
							<td class="cbc-w-action"><button class="cbc-btn cbc-btn-ghost cbc-mb-open" data-id="${r.id}">Open</button></td>
						</tr>
					`).join('');
				}
				const p = $('#cbc-mb-pager');
				p.innerHTML = pager(res.data.page, res.data.pages);
				wirePager(p, n => { state.page = n; load(); });
				$$('.cbc-mb-row', tbody).forEach(c => c.addEventListener('change', e => {
					const id = parseInt(e.target.value, 10);
					if (e.target.checked) state.selected.add(id);
					else state.selected.delete(id);
					e.target.closest('tr').classList.toggle('is-selected', e.target.checked);
					selectionChanged();
				}));
				$$('.cbc-mb-open', tbody).forEach(b => b.addEventListener('click', () => openDrawer(parseInt(b.dataset.id, 10))));
			});
		}

		$('#cbc-mb-refresh').addEventListener('click', load);
		[ '#cbc-mb-status', '#cbc-mb-plan' ].forEach(s => $(s).addEventListener('change', () => { state.page = 1; load(); }));
		$('#cbc-mb-search').addEventListener('input', debounce(() => { state.page = 1; load(); }, 350));
		$('#cbc-mb-all').addEventListener('change', e => {
			$$('.cbc-mb-row', tbody).forEach(c => { c.checked = e.target.checked; c.dispatchEvent(new Event('change')); });
		});

		const bulkAction = $('#cbc-mb-bulk-action');
		const bulkPlan   = $('#cbc-mb-bulk-plan');
		bulkAction.addEventListener('change', () => { bulkPlan.style.display = bulkAction.value === 'plan_change' ? '' : 'none'; });

		$('#cbc-mb-bulk-apply').addEventListener('click', () => {
			const action = bulkAction.value;
			if (!action) return;
			const ids = Array.from(state.selected);
			if (!ids.length) return;
			if (!confirm(`Apply "${action}" to ${ids.length} member(s)?`)) return;
			api('cbc_admin_members_bulk', { ids, bulk: action, plan_id: bulkPlan.value || 0 }).then(res => {
				if (res.success) {
					notice('cbc-admin-notice', res.data.message);
					state.selected.clear();
					selectionChanged();
					load();
				} else {
					notice('cbc-admin-notice', res.data?.message || 'Error', true);
				}
			});
		});

		function openDrawer(id) {
			const drawer = $('#cbc-mb-drawer');
			drawer.hidden = false;
			const body = $('#cbc-mb-drawer-body');
			body.innerHTML = '<div class="cbc-loading">Loading…</div>';
			api('cbc_admin_member_get', { id }).then(res => {
				if (!res.success) {
					body.innerHTML = `<div class="cbc-notice is-error">${escHtml(res.data?.message || 'Error')}</div>`;
					return;
				}
				const m = res.data.member;
				const primary = res.data.primary || {};
				const payments = res.data.payments || [];
				$('#cbc-mb-drawer-title').textContent = m.full_name || primary.full_name || '—';
				$('#cbc-mb-drawer-status').outerHTML  = `<span class="cbc-pill" id="cbc-mb-drawer-status">${statusPill(m.status)}</span>`;
				$('#cbc-mb-drawer-plan').textContent  = `${m.plan_name || ''} · ${m.billing_cycle || ''} · Renews ${(m.renewal_date || '').slice(0,10) || '—'}`;
				body.innerHTML = `
					<section class="cbc-drawer__section">
						<h3>Member basics</h3>
						<div class="cbc-drawer__form">
							<div><label>Full name</label><input type="text" id="cbc-d-name" class="cbc-input" value="${escHtml(m.full_name || primary.full_name || '')}"></div>
							<div><label>Email</label><input type="email" id="cbc-d-email" class="cbc-input" value="${escHtml(m.email || primary.email || '')}"></div>
							<div><label>Phone</label><input type="tel" id="cbc-d-phone" class="cbc-input" value="${escHtml(m.phone || primary.phone || '')}"></div>
							<div><label>Status</label>
								<select id="cbc-d-status" class="cbc-input">
									${['active','pending','past_due','expired','cancelled','paused','trial','comped'].map(s => `<option value="${s}" ${m.status===s?'selected':''}>${s}</option>`).join('')}
								</select>
							</div>
							<div><label>Plan</label>
								<select id="cbc-d-plan" class="cbc-input">
									${(CBC.plans || []).map(p => `<option value="${p.id}" ${parseInt(m.plan_id,10)===parseInt(p.id,10)?'selected':''}>${escHtml(p.name)}</option>`).join('')}
								</select>
							</div>
							<div><label>Billing cycle</label>
								<select id="cbc-d-cycle" class="cbc-input">
									<option value="monthly" ${m.billing_cycle==='monthly'?'selected':''}>Monthly</option>
									<option value="annual" ${m.billing_cycle==='annual'?'selected':''}>Annual</option>
									<option value="lifetime" ${m.billing_cycle==='lifetime'?'selected':''}>Lifetime</option>
								</select>
							</div>
							<div><label>Renewal date</label><input type="date" id="cbc-d-renewal" class="cbc-input" value="${(m.renewal_date || '').slice(0,10)}"></div>
						</div>
						<p style="margin-top:14px;"><button class="cbc-btn cbc-btn-primary" id="cbc-d-save">Save changes</button></p>
					</section>
					<section class="cbc-drawer__section">
						<h3>Recent payments (${payments.length})</h3>
						${payments.length ? `<table class="cbc-table"><thead><tr><th>Amount</th><th>Status</th><th>Method</th><th>Paid</th></tr></thead><tbody>${payments.map(p => `<tr><td>$${parseFloat(p.amount || 0).toFixed(2)} ${escHtml(p.currency || 'USD')}</td><td>${statusPill(p.status)}</td><td>${escHtml(p.method || '—')}</td><td>${escHtml((p.paid_at || p.created_at || '').slice(0,10))}</td></tr>`).join('')}</tbody></table>` : '<p style="color:#6b7280;">No payments recorded.</p>'}
					</section>
				`;
				$('#cbc-d-save').addEventListener('click', () => {
					api('cbc_admin_member_update', {
						id: m.id,
						status:        $('#cbc-d-status').value,
						plan_id:       $('#cbc-d-plan').value,
						billing_cycle: $('#cbc-d-cycle').value,
						renewal_date:  $('#cbc-d-renewal').value ? $('#cbc-d-renewal').value + ' 23:59:59' : '',
					}).then(res => {
						notice('cbc-admin-notice', res.data?.message || (res.success ? 'Saved.' : 'Error'), !res.success);
						if (res.success) { closeDrawer(); load(); }
					});
				});
				$('#cbc-mb-drawer-cancel').onclick = () => {
					if (!confirm('Cancel this membership?')) return;
					api('cbc_admin_members_bulk', { ids: [m.id], bulk: 'cancel' }).then(res => { notice('cbc-admin-notice', res.data?.message || ''); closeDrawer(); load(); });
				};
				$('#cbc-mb-drawer-renew').onclick = () => {
					api('cbc_admin_members_bulk', { ids: [m.id], bulk: m.billing_cycle === 'annual' ? 'renew_year' : 'renew_month' }).then(res => { notice('cbc-admin-notice', res.data?.message || ''); closeDrawer(); load(); });
				};
			});
		}
		function closeDrawer() { $('#cbc-mb-drawer').hidden = true; }
		document.addEventListener('click', e => {
			if (e.target.matches('[data-cbc-close="drawer"]')) closeDrawer();
		});

		load();
	}

	// ── Payments page ────────────────────────────────────────────────────────
	function initPayments() {
		const tbody = $('#cbc-pm-table tbody');
		if (!tbody) return;
		const state = { page: 1, selected: new Set() };

		function selectionChanged() {
			$('#cbc-pm-bulk').hidden = state.selected.size === 0;
			$('#cbc-pm-bulk-count').textContent = state.selected.size + ' selected';
		}

		function load() {
			tbody.innerHTML = `<tr><td colspan="9" class="cbc-loading">Loading…</td></tr>`;
			api('cbc_admin_payments_list', {
				page:      state.page,
				search:    $('#cbc-pm-search').value,
				status:    $('#cbc-pm-status').value,
				date_from: $('#cbc-pm-from').value,
				date_to:   $('#cbc-pm-to').value,
			}).then(res => {
				if (!res.success) {
					tbody.innerHTML = `<tr><td colspan="9" class="cbc-loading">${escHtml(res.data?.message || 'Error')}</td></tr>`;
					return;
				}
				const rows = res.data.rows || [];
				if (!rows.length) {
					tbody.innerHTML = `<tr><td colspan="9" class="cbc-loading">No payments match.</td></tr>`;
				} else {
					tbody.innerHTML = rows.map(r => `
						<tr>
							<td><input type="checkbox" class="cbc-pm-row" value="${r.id}" /></td>
							<td><code>${escHtml(r.membership_uuid || r.membership_id || '')}</code></td>
							<td>${escHtml(r.full_name || r.email || '—')}</td>
							<td>$${parseFloat(r.amount || 0).toFixed(2)} ${escHtml(r.currency || 'USD')}</td>
							<td>${escHtml(r.method || 'Manual')}</td>
							<td>${escHtml(r.gateway || 'Stripe')}</td>
							<td>${statusPill(r.status)}</td>
							<td>${escHtml((r.paid_at || r.created_at || '').slice(0,10))}</td>
							<td class="cbc-w-action">${r.membership_id ? `<a class="cbc-btn-link" href="?page=chatbotistic-connector-members#${r.membership_id}">Open member</a>` : ''}</td>
						</tr>
					`).join('');
				}
				const p = $('#cbc-pm-pager');
				p.innerHTML = pager(res.data.page, res.data.pages);
				wirePager(p, n => { state.page = n; load(); });
				$$('.cbc-pm-row', tbody).forEach(c => c.addEventListener('change', e => {
					const id = parseInt(e.target.value, 10);
					if (e.target.checked) state.selected.add(id); else state.selected.delete(id);
					selectionChanged();
				}));
			});
		}

		$('#cbc-pm-refresh').addEventListener('click', load);
		[ '#cbc-pm-status', '#cbc-pm-from', '#cbc-pm-to' ].forEach(s => $(s).addEventListener('change', () => { state.page = 1; load(); }));
		$('#cbc-pm-search').addEventListener('input', debounce(() => { state.page = 1; load(); }, 350));
		$('#cbc-pm-all').addEventListener('change', e => $$('.cbc-pm-row', tbody).forEach(c => { c.checked = e.target.checked; c.dispatchEvent(new Event('change')); }));

		$('#cbc-pm-bulk-apply').addEventListener('click', () => {
			const action = $('#cbc-pm-bulk-action').value;
			const ids = Array.from(state.selected);
			if (!action || !ids.length) return;
			if (!confirm(`Apply "${action}" to ${ids.length} payment(s)?`)) return;
			api('cbc_admin_payments_bulk', { ids, bulk: action }).then(res => {
				notice('cbc-admin-notice-pay', res.data?.message || '', !res.success);
				state.selected.clear(); selectionChanged(); load();
			});
		});

		$('#cbc-pm-export').addEventListener('click', () => {
			api('cbc_admin_payments_csv', {}).then(res => {
				if (!res.success) return notice('cbc-admin-notice-pay', res.data?.message || 'Error', true);
				const blob = new Blob([res.data.csv], { type: 'text/csv' });
				const url = URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.href = url; a.download = res.data.filename; a.click();
				URL.revokeObjectURL(url);
			});
		});

		load();
	}

	// ── Licenses page ────────────────────────────────────────────────────────
	function initLicenses() {
		const tbody = $('#cbc-lc-table tbody');
		if (!tbody) return;
		const state = { page: 1, selected: new Set() };

		function selectionChanged() {
			$('#cbc-lc-bulk').hidden = state.selected.size === 0;
			$('#cbc-lc-bulk-count').textContent = state.selected.size + ' selected';
		}

		function load() {
			tbody.innerHTML = `<tr><td colspan="8" class="cbc-loading">Loading…</td></tr>`;
			api('cbc_admin_licenses_list', { page: state.page, search: $('#cbc-lc-search').value, status: $('#cbc-lc-status').value }).then(res => {
				if (!res.success) {
					tbody.innerHTML = `<tr><td colspan="8" class="cbc-loading">${escHtml(res.data?.message || 'Error')}</td></tr>`;
					return;
				}
				const rows = res.data.rows || [];
				if (!rows.length) {
					tbody.innerHTML = `<tr><td colspan="8" class="cbc-loading">No licenses yet.</td></tr>`;
					return;
				}
				tbody.innerHTML = rows.map(r => `
					<tr>
						<td><input type="checkbox" class="cbc-lc-row" value="${r.id}" /></td>
						<td><strong>${escHtml(r.customer_email)}</strong></td>
						<td><code>${escHtml(r.key_masked)}</code></td>
						<td>${escHtml(r.plan_name || r.tier || '—')}</td>
						<td>${r.activation_count} / ${r.activation_limit}</td>
						<td>${statusPill(r.status)}</td>
						<td>${escHtml((r.expires_at || '').slice(0,10) || '—')}</td>
						<td class="cbc-w-action"><button class="cbc-btn cbc-btn-ghost cbc-lc-regen" data-id="${r.id}">Regenerate</button></td>
					</tr>
				`).join('');
				$$('.cbc-lc-row', tbody).forEach(c => c.addEventListener('change', e => {
					const id = parseInt(e.target.value, 10);
					if (e.target.checked) state.selected.add(id); else state.selected.delete(id);
					selectionChanged();
				}));
				$$('.cbc-lc-regen', tbody).forEach(b => b.addEventListener('click', () => {
					if (!confirm('Revoke the old key and mint a new one for this customer?')) return;
					api('cbc_admin_license_regen', { id: b.dataset.id }).then(res => {
						if (res.success) {
							notice('cbc-admin-notice-lc', 'New key: ' + res.data.new_key);
							load();
						} else {
							notice('cbc-admin-notice-lc', res.data?.message || 'Error', true);
						}
					});
				}));
			});
		}

		$('#cbc-lc-refresh').addEventListener('click', load);
		$('#cbc-lc-status').addEventListener('change', () => { state.page = 1; load(); });
		$('#cbc-lc-search').addEventListener('input', debounce(() => { state.page = 1; load(); }, 350));
		$('#cbc-lc-all').addEventListener('change', e => $$('.cbc-lc-row', tbody).forEach(c => { c.checked = e.target.checked; c.dispatchEvent(new Event('change')); }));

		$('#cbc-lc-bulk-apply').addEventListener('click', () => {
			const action = $('#cbc-lc-bulk-action').value;
			const ids = Array.from(state.selected);
			if (!action || !ids.length) return;
			if (!confirm(`Apply "${action}" to ${ids.length} license(s)?`)) return;
			api('cbc_admin_licenses_bulk', { ids, bulk: action }).then(res => {
				notice('cbc-admin-notice-lc', res.data?.message || '', !res.success);
				state.selected.clear(); selectionChanged(); load();
			});
		});

		load();
	}

	document.addEventListener('DOMContentLoaded', () => {
		initMembers();
		initPayments();
		initLicenses();
	});
})();
