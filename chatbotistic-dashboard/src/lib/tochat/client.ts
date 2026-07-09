import "server-only";

/**
 * Tochat.be (ChatWith) REST API client — TypeScript port of the
 * Chatbotistic Connector's PHP `class-api.php`.
 *
 * - Authenticates with the master account (`TOCHAT_API_EMAIL` /
 *   `TOCHAT_API_PASSWORD`), caches the JWT for ~50 minutes, and
 *   retries once on 401 so an expired token never surfaces to users.
 * - Flattens Hydra collections to plain arrays.
 * - Multi-tenancy rides on the `userClient` tag: every org maps to
 *   `org-{orgId}`, and every write verifies ownership by re-fetching
 *   the resource and comparing userClient (`ownedWidget` /
 *   `ownedOperator` guards).
 */

import {
  TochatApiError,
  type HydraCollection,
  type TochatBanner,
  type TochatBookingConfig,
  type TochatCampaign,
  type TochatExportedLead,
  type TochatFaqGroup,
  type TochatLandingLink,
  type TochatManyContact,
  type TochatOperator,
  type TochatResource,
  type TochatStat,
  type TochatWidget,
  type TochatWidgetRule,
} from "./types";

const TOKEN_TTL_MS = 50 * 60 * 1000; // ~50 minutes

function apiBase(): string {
  return (process.env.TOCHAT_API_BASE || "https://services.tochat.be").replace(
    /\/$/,
    ""
  );
}

/** userClient tag for an organization — the tenancy boundary. */
export function userClientForOrg(orgId: string): string {
  return `org-${orgId}`;
}

// ── Token cache (survives module re-evaluation via globalThis) ────────

interface TokenCache {
  token: string;
  expiresAt: number;
}

const g = globalThis as typeof globalThis & {
  __tochatTokenCache?: TokenCache | null;
};

async function getToken(force = false): Promise<string> {
  if (!force) {
    const cached = g.__tochatTokenCache;
    if (cached && cached.expiresAt > Date.now()) return cached.token;
  }

  const email = process.env.TOCHAT_API_EMAIL;
  const password = process.env.TOCHAT_API_PASSWORD;
  if (!email || !password) {
    throw new TochatApiError(
      "Tochat API credentials are not configured (TOCHAT_API_EMAIL / TOCHAT_API_PASSWORD).",
      500
    );
  }

  const res = await http("/api/authentication_token", "POST", { email, password }, null);
  const token =
    typeof res.body === "object" && res.body !== null
      ? (res.body as { token?: string }).token
      : undefined;
  if (res.status < 200 || res.status >= 300 || !token) {
    g.__tochatTokenCache = null;
    throw new TochatApiError(
      "Tochat did not return an authentication token.",
      res.status >= 400 ? res.status : 502
    );
  }

  g.__tochatTokenCache = { token, expiresAt: Date.now() + TOKEN_TTL_MS };
  return token;
}

export function clearToken(): void {
  g.__tochatTokenCache = null;
}

// ── Request plumbing ──────────────────────────────────────────────────

interface HttpResult {
  status: number;
  body: unknown;
}

async function http(
  endpoint: string,
  method: string,
  body: Record<string, unknown> | null,
  token: string | null
): Promise<HttpResult> {
  const headers: Record<string, string> = {
    Accept: "application/json",
    "Content-Type":
      method.toUpperCase() === "PATCH"
        ? "application/merge-patch+json"
        : "application/json",
  };
  if (token) headers.Authorization = `Bearer ${token}`;

  const init: RequestInit = {
    method: method.toUpperCase(),
    headers,
    cache: "no-store",
    signal: AbortSignal.timeout(30_000),
  };
  if (body !== null && ["POST", "PUT", "PATCH"].includes(method.toUpperCase())) {
    init.body = JSON.stringify(body);
  }

  const response = await fetch(apiBase() + endpoint, init);
  const text = await response.text();
  let parsed: unknown = null;
  if (text) {
    try {
      parsed = JSON.parse(text);
    } catch {
      parsed = null;
    }
  }
  return { status: response.status, body: parsed };
}

function errorMessage(status: number, body: unknown): string {
  if (typeof body === "object" && body !== null) {
    const b = body as Record<string, unknown>;
    const violation = Array.isArray(b.violations)
      ? (b.violations[0] as Record<string, unknown> | undefined)?.message
      : undefined;
    const msg =
      (b.detail as string) ||
      (b["hydra:description"] as string) ||
      (b.message as string) ||
      (violation as string);
    if (msg) return String(msg);
  }
  return `Tochat API error (HTTP ${status}).`;
}

/**
 * Authenticated request with a one-shot retry on token expiry.
 * DELETE resolves to `true`; everything else to the decoded body.
 */
async function request<T = TochatResource>(
  endpoint: string,
  method = "GET",
  body: Record<string, unknown> | null = null
): Promise<T> {
  let token = await getToken();
  let res = await http(endpoint, method, body, token);

  // Token expired mid-flight — refresh once and retry.
  if (res.status === 401) {
    clearToken();
    token = await getToken(true);
    res = await http(endpoint, method, body, token);
  }

  if (res.status < 200 || res.status >= 300) {
    throw new TochatApiError(errorMessage(res.status, res.body), res.status);
  }

  if (method.toUpperCase() === "DELETE") {
    return true as unknown as T;
  }
  return (res.body ?? {}) as T;
}

/** Flatten a Hydra collection to its member array. */
function collection<T>(result: HydraCollection<T> | T[] | unknown): T[] {
  if (Array.isArray(result)) return result as T[];
  if (
    typeof result === "object" &&
    result !== null &&
    Array.isArray((result as HydraCollection<T>)["hydra:member"])
  ) {
    return (result as HydraCollection<T>)["hydra:member"] as T[];
  }
  return [];
}

function qs(params: Record<string, string | number | undefined>): string {
  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== "") search.append(key, String(value));
  }
  return search.toString();
}

/** Extract a resource ID from a create/get response (id or @id IRI). */
export function resourceId(resource: TochatResource): string {
  if (resource.id) return String(resource.id);
  if (resource.uuid) return String(resource.uuid);
  if (resource["@id"]) {
    const iri = String(resource["@id"]).replace(/\/$/, "");
    return iri.slice(iri.lastIndexOf("/") + 1);
  }
  return "";
}

// ── Ownership guards ──────────────────────────────────────────────────

/**
 * Fetch a widget and verify it belongs to the given userClient.
 * Throws 404 (not 403 — don't leak existence) on any mismatch.
 * EVERY per-widget write goes through this.
 */
export async function ownedWidget(
  widgetId: string,
  userClient: string
): Promise<TochatWidget> {
  const widget = await request<TochatWidget>(
    `/api/v2/widgets/${encodeURIComponent(widgetId)}`
  );
  if (!widget || widget.userClient !== userClient) {
    throw new TochatApiError("Widget not found.", 404);
  }
  return widget;
}

/**
 * Fetch an operator and verify its parent widget belongs to the
 * userClient. Resolves the `business` relation whether it arrives
 * embedded or as an IRI.
 */
export async function ownedOperator(
  operatorId: string,
  userClient: string
): Promise<TochatOperator> {
  const operator = await request<TochatOperator>(
    `/api/v2/whatsapp_operators/${encodeURIComponent(operatorId)}`
  );
  const business = operator?.business;
  let owner: string | undefined;
  if (typeof business === "object" && business !== null) {
    owner = business.userClient;
  } else if (typeof business === "string") {
    const iri = business.replace(/\/$/, "");
    const widgetId = iri.slice(iri.lastIndexOf("/") + 1);
    const widget = await request<TochatWidget>(
      `/api/v2/widgets/${encodeURIComponent(widgetId)}`
    );
    owner = widget?.userClient;
  }
  if (owner !== userClient) {
    throw new TochatApiError("Agent not found.", 404);
  }
  return operator;
}

// ── Widgets ───────────────────────────────────────────────────────────

export async function listWidgets(userClient: string): Promise<TochatWidget[]> {
  const res = await request<HydraCollection<TochatWidget>>(
    `/api/v2/widgets?${qs({ "userClient[]": userClient, itemsPerPage: 100 })}`
  );
  return collection<TochatWidget>(res);
}

export async function getWidget(
  id: string,
  userClient: string
): Promise<TochatWidget> {
  return ownedWidget(id, userClient);
}

export async function createWidget(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatWidget> {
  // The tenancy tag is server-assigned — never trust the caller's.
  return request<TochatWidget>("/api/v2/widgets", "POST", {
    ...payload,
    userClient,
  });
}

export async function updateWidget(
  id: string,
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatWidget> {
  await ownedWidget(id, userClient);
  return request<TochatWidget>(
    `/api/v2/widgets/${encodeURIComponent(id)}`,
    "PUT",
    { ...payload, userClient }
  );
}

export async function patchWidget(
  id: string,
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatWidget> {
  await ownedWidget(id, userClient);
  const { userClient: _ignored, ...rest } = payload;
  void _ignored;
  return request<TochatWidget>(
    `/api/v2/widgets/${encodeURIComponent(id)}`,
    "PATCH",
    rest
  );
}

export async function deleteWidget(
  id: string,
  userClient: string
): Promise<boolean> {
  await ownedWidget(id, userClient);
  return request<boolean>(`/api/v2/widgets/${encodeURIComponent(id)}`, "DELETE");
}

// ── WhatsApp operators (agents) ───────────────────────────────────────

export async function listOperators(
  userClient: string
): Promise<TochatOperator[]> {
  const res = await request<HydraCollection<TochatOperator>>(
    `/api/v2/whatsapp_operators?${qs({
      "business.userClient[]": userClient,
      itemsPerPage: 200,
    })}`
  );
  return collection<TochatOperator>(res);
}

export async function operatorsForWidget(
  widgetId: string,
  userClient: string
): Promise<TochatOperator[]> {
  await ownedWidget(widgetId, userClient);
  const res = await request<HydraCollection<TochatOperator>>(
    `/api/v2/whatsapp_operators?${qs({ "business.id": widgetId, itemsPerPage: 200 })}`
  );
  return collection<TochatOperator>(res);
}

export async function getOperator(
  id: string,
  userClient: string
): Promise<TochatOperator> {
  return ownedOperator(id, userClient);
}

export async function createOperator(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatOperator> {
  // The operator's parent widget must belong to the org.
  const business = String(payload.business ?? "");
  const widgetId = business.replace(/\/$/, "").split("/").pop() ?? "";
  if (!widgetId) throw new TochatApiError("Missing widget for agent.", 400);
  await ownedWidget(widgetId, userClient);
  return request<TochatOperator>("/api/v2/whatsapp_operators", "POST", payload);
}

export async function updateOperator(
  id: string,
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatOperator> {
  await ownedOperator(id, userClient);
  return request<TochatOperator>(
    `/api/v2/whatsapp_operators/${encodeURIComponent(id)}`,
    "PUT",
    payload
  );
}

export async function deleteOperator(
  id: string,
  userClient: string
): Promise<boolean> {
  await ownedOperator(id, userClient);
  return request<boolean>(
    `/api/v2/whatsapp_operators/${encodeURIComponent(id)}`,
    "DELETE"
  );
}

// ── FAQ groups ────────────────────────────────────────────────────────

export async function listFaqs(
  operatorId: string,
  userClient: string
): Promise<TochatFaqGroup[]> {
  await ownedOperator(operatorId, userClient);
  const res = await request<HydraCollection<TochatFaqGroup>>(
    `/api/v2/whatsapp_operators/${encodeURIComponent(operatorId)}/faq_grps`
  );
  return collection<TochatFaqGroup>(res);
}

export async function createFaq(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatFaqGroup> {
  const whatsapp = String(payload.whatsapp ?? "");
  const operatorId = whatsapp.replace(/\/$/, "").split("/").pop() ?? "";
  if (!operatorId) throw new TochatApiError("Missing agent for FAQ.", 400);
  await ownedOperator(operatorId, userClient);
  return request<TochatFaqGroup>("/api/v2/faq_grps", "POST", payload);
}

export async function updateFaq(
  id: string,
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatFaqGroup> {
  // Verify via the FAQ's parent operator.
  const existing = await request<TochatFaqGroup>(
    `/api/v2/faq_grps/${encodeURIComponent(id)}`
  );
  const parent = String(
    typeof existing.whatsapp === "object" && existing.whatsapp !== null
      ? (existing.whatsapp["@id"] ?? existing.whatsapp.id ?? "")
      : (existing.whatsapp ?? "")
  );
  const operatorId = parent.replace(/\/$/, "").split("/").pop() ?? "";
  if (!operatorId) throw new TochatApiError("FAQ not found.", 404);
  await ownedOperator(operatorId, userClient);
  return request<TochatFaqGroup>(
    `/api/v2/faq_grps/${encodeURIComponent(id)}`,
    "PUT",
    payload
  );
}

export async function deleteFaq(
  id: string,
  userClient: string
): Promise<boolean> {
  const existing = await request<TochatFaqGroup>(
    `/api/v2/faq_grps/${encodeURIComponent(id)}`
  );
  const parent = String(
    typeof existing.whatsapp === "object" && existing.whatsapp !== null
      ? (existing.whatsapp["@id"] ?? existing.whatsapp.id ?? "")
      : (existing.whatsapp ?? "")
  );
  const operatorId = parent.replace(/\/$/, "").split("/").pop() ?? "";
  if (!operatorId) throw new TochatApiError("FAQ not found.", 404);
  await ownedOperator(operatorId, userClient);
  return request<boolean>(`/api/v2/faq_grps/${encodeURIComponent(id)}`, "DELETE");
}

// ── Leads (stats) + analytics ─────────────────────────────────────────

/**
 * List leads. The userClient filter is forced — callers may add
 * widget/date filters but can never widen the tenancy scope.
 */
export async function getLeads(
  userClient: string,
  filters: Record<string, string | number | undefined> = {}
): Promise<TochatStat[]> {
  const res = await request<HydraCollection<TochatStat>>(
    `/api/v2/stats?${qs({
      itemsPerPage: 100,
      ...filters,
      "business.userClient[]": userClient,
    })}`
  );
  return collection<TochatStat>(res);
}

/** Visits/clicks/leads graph for one widget. type: day|week. */
export async function statsGraph(
  widgetId: string,
  userClient: string,
  from: string,
  to: string,
  type: "day" | "week" = "day"
): Promise<unknown> {
  await ownedWidget(widgetId, userClient);
  return request<unknown>(
    `/api/v2/${encodeURIComponent(widgetId)}/stats-graph?${qs({ from, to, type })}`
  );
}

/** Referer attribution graph for one widget. */
export async function refererGraph(
  widgetId: string,
  userClient: string,
  from: string,
  to: string
): Promise<unknown> {
  await ownedWidget(widgetId, userClient);
  return request<unknown>(
    `/api/v2/${encodeURIComponent(widgetId)}/referer-graph?${qs({ from, to })}`
  );
}

/** Aggregate widget stats (visits/clicks/leads counters). */
export async function widgetStats(
  widgetId: string,
  userClient: string
): Promise<unknown> {
  await ownedWidget(widgetId, userClient);
  // Path shape matches the Chatbotistic Widget plugin's analytics client
  // (GET /api/v2/widget_stats/{id}) — the previous /{id}/widget-stats
  // shape is not a Tochat route.
  return request<unknown>(`/api/v2/widget_stats/${encodeURIComponent(widgetId)}`);
}

// ── Landing links ─────────────────────────────────────────────────────

export async function landingLinks(
  widgetId: string,
  userClient: string
): Promise<TochatLandingLink[]> {
  await ownedWidget(widgetId, userClient);
  const res = await request<HydraCollection<TochatLandingLink> | TochatLandingLink[]>(
    `/api/landing-links/${encodeURIComponent(widgetId)}`
  );
  return collection<TochatLandingLink>(res);
}

// ── Campaigns (broadcast / drip on Tochat's side) ─────────────────────

export async function listCampaigns(
  userClient: string
): Promise<TochatCampaign[]> {
  const res = await request<HydraCollection<TochatCampaign>>(
    `/api/v2/campaigns?${qs({
      "business.userClient[]": userClient,
      itemsPerPage: 100,
    })}`
  );
  return collection<TochatCampaign>(res);
}

export async function createCampaign(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatCampaign> {
  const business = String(payload.business ?? "");
  const widgetId = business.replace(/\/$/, "").split("/").pop() ?? "";
  if (widgetId) await ownedWidget(widgetId, userClient);
  return request<TochatCampaign>("/api/v2/campaigns", "POST", payload);
}

export async function updateCampaign(
  id: string,
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatCampaign> {
  const existing = await request<TochatCampaign>(
    `/api/v2/campaigns/${encodeURIComponent(id)}`
  );
  await assertBusinessOwned(existing.business, userClient, "Campaign");
  return request<TochatCampaign>(
    `/api/v2/campaigns/${encodeURIComponent(id)}`,
    "PUT",
    payload
  );
}

export async function deleteCampaign(
  id: string,
  userClient: string
): Promise<boolean> {
  const existing = await request<TochatCampaign>(
    `/api/v2/campaigns/${encodeURIComponent(id)}`
  );
  await assertBusinessOwned(existing.business, userClient, "Campaign");
  return request<boolean>(`/api/v2/campaigns/${encodeURIComponent(id)}`, "DELETE");
}

// ── Audiences / many_contacts ─────────────────────────────────────────

export async function listManyContacts(
  userClient: string
): Promise<TochatManyContact[]> {
  const res = await request<HydraCollection<TochatManyContact>>(
    `/api/v2/many_contacts?${qs({
      "business.userClient[]": userClient,
      itemsPerPage: 100,
    })}`
  );
  return collection<TochatManyContact>(res);
}

export async function createManyContact(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatManyContact> {
  const business = String(payload.business ?? "");
  const widgetId = business.replace(/\/$/, "").split("/").pop() ?? "";
  if (widgetId) await ownedWidget(widgetId, userClient);
  return request<TochatManyContact>("/api/v2/many_contacts", "POST", payload);
}

export async function deleteManyContact(
  id: string,
  userClient: string
): Promise<boolean> {
  const existing = await request<TochatManyContact>(
    `/api/v2/many_contacts/${encodeURIComponent(id)}`
  );
  await assertBusinessOwned(existing.business, userClient, "Audience");
  return request<boolean>(
    `/api/v2/many_contacts/${encodeURIComponent(id)}`,
    "DELETE"
  );
}

// ── Booking configs ───────────────────────────────────────────────────

export async function listBookingConfigs(
  operatorId: string,
  userClient: string
): Promise<TochatBookingConfig[]> {
  await ownedOperator(operatorId, userClient);
  const res = await request<HydraCollection<TochatBookingConfig>>(
    `/api/v2/booking_configs?${qs({ whatsapp: operatorId })}`
  );
  return collection<TochatBookingConfig>(res);
}

export async function createBookingConfig(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatBookingConfig> {
  const whatsapp = String(payload.whatsapp ?? "");
  const operatorId = whatsapp.replace(/\/$/, "").split("/").pop() ?? "";
  if (!operatorId) throw new TochatApiError("Missing agent for booking.", 400);
  await ownedOperator(operatorId, userClient);
  return request<TochatBookingConfig>("/api/v2/booking_configs", "POST", payload);
}

export async function updateBookingConfig(
  id: string,
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatBookingConfig> {
  const existing = await request<TochatBookingConfig>(
    `/api/v2/booking_configs/${encodeURIComponent(id)}`
  );
  const parent = String(
    typeof existing.whatsapp === "object" && existing.whatsapp !== null
      ? (existing.whatsapp["@id"] ?? existing.whatsapp.id ?? "")
      : (existing.whatsapp ?? "")
  );
  const operatorId = parent.replace(/\/$/, "").split("/").pop() ?? "";
  if (!operatorId) throw new TochatApiError("Booking config not found.", 404);
  await ownedOperator(operatorId, userClient);
  return request<TochatBookingConfig>(
    `/api/v2/booking_configs/${encodeURIComponent(id)}`,
    "PUT",
    payload
  );
}

export async function deleteBookingConfig(
  id: string,
  userClient: string
): Promise<boolean> {
  const existing = await request<TochatBookingConfig>(
    `/api/v2/booking_configs/${encodeURIComponent(id)}`
  );
  const parent = String(
    typeof existing.whatsapp === "object" && existing.whatsapp !== null
      ? (existing.whatsapp["@id"] ?? existing.whatsapp.id ?? "")
      : (existing.whatsapp ?? "")
  );
  const operatorId = parent.replace(/\/$/, "").split("/").pop() ?? "";
  if (!operatorId) throw new TochatApiError("Booking config not found.", 404);
  await ownedOperator(operatorId, userClient);
  return request<boolean>(
    `/api/v2/booking_configs/${encodeURIComponent(id)}`,
    "DELETE"
  );
}

// ── Banners ───────────────────────────────────────────────────────────

export async function listBanners(
  widgetId: string,
  userClient: string
): Promise<TochatBanner[]> {
  await ownedWidget(widgetId, userClient);
  const res = await request<HydraCollection<TochatBanner>>(
    `/api/v2/banners?${qs({ "business.id": widgetId })}`
  );
  return collection<TochatBanner>(res);
}

export async function createBanner(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatBanner> {
  const business = String(payload.business ?? "");
  const widgetId = business.replace(/\/$/, "").split("/").pop() ?? "";
  if (!widgetId) throw new TochatApiError("Missing widget for banner.", 400);
  await ownedWidget(widgetId, userClient);
  return request<TochatBanner>("/api/v2/banners", "POST", payload);
}

export async function updateBanner(
  id: string,
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatBanner> {
  const existing = await request<TochatBanner>(
    `/api/v2/banners/${encodeURIComponent(id)}`
  );
  await assertBusinessOwned(existing.business, userClient, "Banner");
  return request<TochatBanner>(
    `/api/v2/banners/${encodeURIComponent(id)}`,
    "PUT",
    payload
  );
}

export async function deleteBanner(
  id: string,
  userClient: string
): Promise<boolean> {
  const existing = await request<TochatBanner>(
    `/api/v2/banners/${encodeURIComponent(id)}`
  );
  await assertBusinessOwned(existing.business, userClient, "Banner");
  return request<boolean>(`/api/v2/banners/${encodeURIComponent(id)}`, "DELETE");
}

// ── Widget targeting / display rules ──────────────────────────────────

export async function listWidgetRules(
  widgetId: string,
  userClient: string
): Promise<TochatWidgetRule[]> {
  await ownedWidget(widgetId, userClient);
  const res = await request<HydraCollection<TochatWidgetRule>>(
    `/api/v2/widget_rules?${qs({ "widget.uuid": widgetId })}`
  );
  return collection<TochatWidgetRule>(res);
}

export async function createWidgetRule(
  payload: Record<string, unknown>,
  userClient: string
): Promise<TochatWidgetRule> {
  const widget = String(payload.widget ?? "");
  const widgetId = widget.replace(/\/$/, "").split("/").pop() ?? "";
  if (!widgetId) throw new TochatApiError("Missing widget for rule.", 400);
  await ownedWidget(widgetId, userClient);
  return request<TochatWidgetRule>("/api/v2/widget_rules", "POST", payload);
}

export async function deleteWidgetRule(
  id: string,
  userClient: string
): Promise<boolean> {
  const existing = await request<TochatWidgetRule>(
    `/api/v2/widget_rules/${encodeURIComponent(id)}`
  );
  const parent = String(
    typeof existing.widget === "object" && existing.widget !== null
      ? (existing.widget["@id"] ?? existing.widget.uuid ?? "")
      : (existing.widget ?? "")
  );
  const widgetId = parent.replace(/\/$/, "").split("/").pop() ?? "";
  if (!widgetId) throw new TochatApiError("Rule not found.", 404);
  await ownedWidget(widgetId, userClient);
  return request<boolean>(
    `/api/v2/widget_rules/${encodeURIComponent(id)}`,
    "DELETE"
  );
}

// ── Lead export via long-lived API key ────────────────────────────────
//
// Uses a *separate* key (`TOCHAT_LEAD_API_KEY`, not the JWT):
//   GET /api/get-json-lead?fromDate=YYYY-MM-DD&page=1

export async function exportLeads(
  fromDate = "",
  page = 1,
  toDate?: string
): Promise<TochatExportedLead[]> {
  const apiKey = process.env.TOCHAT_LEAD_API_KEY;
  if (!apiKey) {
    throw new TochatApiError(
      "Lead Export API Key is not configured (TOCHAT_LEAD_API_KEY).",
      500
    );
  }
  const params: Record<string, string | number | undefined> = {
    page: Math.max(1, page),
  };
  if (fromDate) params.fromDate = fromDate;
  if (toDate) params.toDate = toDate;

  const res = await http(`/api/get-json-lead?${qs(params)}`, "GET", null, apiKey);
  if (res.status < 200 || res.status >= 300) {
    throw new TochatApiError(errorMessage(res.status, res.body), res.status);
  }
  return collection<TochatExportedLead>(res.body);
}

// ── Shared relation-ownership helper ──────────────────────────────────

async function assertBusinessOwned(
  business: string | TochatWidget | undefined,
  userClient: string,
  label: string
): Promise<void> {
  let owner: string | undefined;
  if (typeof business === "object" && business !== null) {
    owner = business.userClient;
    if (!owner) {
      const id = resourceId(business);
      if (id) {
        const widget = await request<TochatWidget>(
          `/api/v2/widgets/${encodeURIComponent(id)}`
        );
        owner = widget?.userClient;
      }
    }
  } else if (typeof business === "string" && business) {
    const iri = business.replace(/\/$/, "");
    const widgetId = iri.slice(iri.lastIndexOf("/") + 1);
    const widget = await request<TochatWidget>(
      `/api/v2/widgets/${encodeURIComponent(widgetId)}`
    );
    owner = widget?.userClient;
  }
  if (owner !== userClient) {
    throw new TochatApiError(`${label} not found.`, 404);
  }
}
