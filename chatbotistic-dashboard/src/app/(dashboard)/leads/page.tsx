import { Card, CardContent } from "@/components/ui/card";
import { LeadsPageClient, type LeadRow } from "@/components/leads/leads-page-client";
import { requireOrgContext, OrgContextError } from "@/lib/org-context";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Leads",
};

/**
 * Server component: fetches all of the org's leads + a widget-id → name
 * map once per request, then hands everything to a client component
 * that does search/filter/sort/CSV export in the browser.
 *
 * Design choice — fetch-all-then-filter-client-side rather than
 * server-side query params: the leads table is expected to stay in
 * the low thousands per org (message_usage caps monthly volume well
 * below what would make an unpaginated fetch expensive), and this
 * keeps every filter (search, widget, status, date range, "new only")
 * instantaneous with no round trip. If lead volume grows enough for
 * this to matter, `GET /api/leads` already supports
 * `status`/`widgetId`/`limit`/`offset` server-side filtering and this
 * page can switch to it without changing the client component's
 * filtering UI.
 */
export default async function LeadsPage() {
  try {
    const ctx = await requireOrgContext();

    const [{ data: leads, error: leadsError }, { data: widgets }] = await Promise.all([
      ctx.supabase
        .from("leads")
        .select("*")
        .eq("org_id", ctx.org.id)
        .order("created_at", { ascending: false }),
      ctx.supabase
        .from("widgets_cache")
        .select("tochat_widget_id, name")
        .eq("org_id", ctx.org.id),
    ]);

    if (leadsError) {
      throw new Error(leadsError.message);
    }

    const widgetNames: Record<string, string> = {};
    for (const w of widgets ?? []) {
      if (w.tochat_widget_id) widgetNames[w.tochat_widget_id] = w.name || w.tochat_widget_id;
    }

    return (
      <LeadsPageClient
        initialLeads={(leads ?? []) as LeadRow[]}
        widgetNames={widgetNames}
      />
    );
  } catch (err) {
    const message = err instanceof OrgContextError ? err.message : "Couldn't load leads.";
    return (
      <div className="flex flex-col gap-6">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Leads</h1>
        </div>
        <Card>
          <CardContent className="pt-5 text-sm text-muted-foreground">{message}</CardContent>
        </Card>
      </div>
    );
  }
}
