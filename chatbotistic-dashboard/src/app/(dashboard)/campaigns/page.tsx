import { Card, CardContent } from "@/components/ui/card";
import { CampaignsPageClient, type CampaignRow } from "@/components/campaigns/campaigns-page-client";
import { requireOrgContext, OrgContextError } from "@/lib/org-context";
import { currentPeriodMonth, limitOf } from "@/lib/plans";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Campaigns",
};

export default async function CampaignsPage() {
  try {
    const ctx = await requireOrgContext();
    const period = currentPeriodMonth();

    const [{ data: campaigns, error: campaignsError }, { data: connection }, { data: usage }, { data: leads }, { data: widgets }] =
      await Promise.all([
        ctx.supabase
          .from("campaigns")
          .select("id, name, status, message_body, audience, scheduled_at, sent_count, created_at")
          .eq("org_id", ctx.org.id)
          .order("created_at", { ascending: false }),
        ctx.supabase
          .from("whatsapp_connections")
          .select("status")
          .eq("org_id", ctx.org.id)
          .maybeSingle(),
        ctx.supabase
          .from("message_usage")
          .select("messages_sent")
          .eq("org_id", ctx.org.id)
          .eq("period_month", period)
          .maybeSingle(),
        ctx.supabase.from("leads").select("id, status, widget_id").eq("org_id", ctx.org.id),
        ctx.supabase.from("widgets_cache").select("tochat_widget_id, name").eq("org_id", ctx.org.id),
      ]);

    if (campaignsError) throw new Error(campaignsError.message);

    const widgetNames: Record<string, string> = {};
    for (const w of widgets ?? []) {
      if (w.tochat_widget_id) widgetNames[w.tochat_widget_id] = w.name || w.tochat_widget_id;
    }

    return (
      <CampaignsPageClient
        orgId={ctx.org.id}
        campaigns={(campaigns ?? []) as CampaignRow[]}
        leads={leads ?? []}
        widgetNames={widgetNames}
        connectionStatus={connection?.status === "connected" ? "connected" : "disconnected"}
        messagesUsed={usage?.messages_sent ?? 0}
        messagesLimit={limitOf(ctx.org, "messages_per_month")}
      />
    );
  } catch (err) {
    const message = err instanceof OrgContextError ? err.message : "Couldn't load campaigns.";
    return (
      <div className="flex flex-col gap-6">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Campaigns</h1>
        </div>
        <Card>
          <CardContent className="pt-5 text-sm text-muted-foreground">{message}</CardContent>
        </Card>
      </div>
    );
  }
}
