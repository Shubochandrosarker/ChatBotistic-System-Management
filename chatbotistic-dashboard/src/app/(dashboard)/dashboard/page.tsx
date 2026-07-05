import { Badge, type BadgeVariant } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { StaggerItem, StaggerList } from "@/components/motion/stagger-list";
import { UsageMeter } from "@/components/campaigns/usage-meter";
import { requireOrgContext, OrgContextError } from "@/lib/org-context";
import { currentPeriodMonth, limitOf } from "@/lib/plans";
import { formatDateTime, formatNumber } from "@/lib/utils";
import { listOperators } from "@/lib/tochat/client";
import { CalendarCheck, CheckCircle2, Circle, LayoutGrid, MessageCircle, Plus, Users } from "lucide-react";
import Link from "next/link";
import type { Metadata } from "next";
import { ChecklistProgress } from "./checklist-progress";
import { SyncLeadsButton } from "./sync-leads-button";

export const metadata: Metadata = {
  title: "Dashboard",
};

const STATUS_BADGE: Record<string, BadgeVariant> = {
  open: "outline",
  contacted: "warning",
  won: "success",
  lost: "destructive",
};

const STATUS_LABEL: Record<string, string> = {
  open: "New",
  contacted: "Contacted",
  won: "Won",
  lost: "Lost",
};

interface ChecklistStep {
  label: string;
  done: boolean;
  href: string;
}

export default async function DashboardPage() {
  try {
    const ctx = await requireOrgContext();
    const period = currentPeriodMonth();
    const weekAgo = new Date(Date.now() - 7 * 86_400_000).toISOString();

    const [
      { count: widgetsCount },
      { data: activeWidgets },
      { count: leadsThisWeek },
      { count: openLeadsCount },
      { data: recentLeads },
      { data: usage },
      { count: membersCount },
      { data: connection },
      { count: campaignsCount },
    ] = await Promise.all([
      ctx.supabase.from("widgets_cache").select("id", { count: "exact", head: true }).eq("org_id", ctx.org.id),
      ctx.supabase.from("widgets_cache").select("id").eq("org_id", ctx.org.id).eq("active", true).limit(1),
      ctx.supabase
        .from("leads")
        .select("id", { count: "exact", head: true })
        .eq("org_id", ctx.org.id)
        .gte("created_at", weekAgo),
      ctx.supabase
        .from("leads")
        .select("id", { count: "exact", head: true })
        .eq("org_id", ctx.org.id)
        .eq("status", "open"),
      ctx.supabase
        .from("leads")
        .select("id, name, phone, status, created_at")
        .eq("org_id", ctx.org.id)
        .order("created_at", { ascending: false })
        .limit(5),
      ctx.supabase
        .from("message_usage")
        .select("messages_sent")
        .eq("org_id", ctx.org.id)
        .eq("period_month", period)
        .maybeSingle(),
      ctx.supabase.from("org_members").select("id", { count: "exact", head: true }).eq("org_id", ctx.org.id),
      ctx.supabase.from("whatsapp_connections").select("status").eq("org_id", ctx.org.id).maybeSingle(),
      ctx.supabase.from("campaigns").select("id", { count: "exact", head: true }).eq("org_id", ctx.org.id),
    ]);

    // Best-effort — agents (Tochat "whatsapp_operators") live entirely
    // on Tochat's side, no local mirror table exists for them (unlike
    // widgets_cache). This is the checklist's only network call to
    // Tochat; if it's unreachable or credentials aren't configured in
    // this environment, treat the step as not-yet-done rather than
    // failing the whole dashboard.
    let hasAgent = false;
    try {
      const operators = await listOperators(ctx.userClient);
      hasAgent = operators.length > 0;
    } catch {
      hasAgent = false;
    }

    const messagesUsed = usage?.messages_sent ?? 0;
    const messagesLimit = limitOf(ctx.org, "messages_per_month");
    const whatsappConnected = connection?.status === "connected";

    const steps: ChecklistStep[] = [
      { label: "Create your first widget", done: (widgetsCount ?? 0) > 0, href: "/widgets" },
      { label: "Add an agent", done: hasAgent, href: "/widgets" },
      { label: "Embed it on your site", done: (activeWidgets ?? []).length > 0, href: "/widgets" },
      { label: "Connect WhatsApp", done: whatsappConnected, href: "/settings?tab=whatsapp" },
      { label: "Send your first campaign", done: (campaignsCount ?? 0) > 0, href: "/campaigns" },
    ];
    const doneCount = steps.filter((s) => s.done).length;

    return (
      <div className="flex flex-col gap-6">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
            <p className="mt-1 text-sm text-muted-foreground">
              Welcome back — here&apos;s what&apos;s happening across your widgets.
            </p>
          </div>
          <div className="flex items-center gap-2">
            <SyncLeadsButton />
            <Link href="/widgets">
              <Button>
                <Plus className="h-4 w-4" />
                Create widget
              </Button>
            </Link>
          </div>
        </div>

        <StaggerList className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StaggerItem>
            <Card>
              <CardHeader>
                <p className="flex items-center gap-1.5 text-sm font-medium text-muted-foreground">
                  <LayoutGrid className="h-4 w-4" /> Active widgets
                </p>
              </CardHeader>
              <CardContent className="pt-3">
                <p className="text-3xl font-semibold tracking-tight">{formatNumber(widgetsCount ?? 0)}</p>
              </CardContent>
            </Card>
          </StaggerItem>
          <StaggerItem>
            <Card>
              <CardHeader>
                <p className="flex items-center gap-1.5 text-sm font-medium text-muted-foreground">
                  <Users className="h-4 w-4" /> Leads this week
                </p>
              </CardHeader>
              <CardContent className="pt-3">
                <p className="text-3xl font-semibold tracking-tight">{formatNumber(leadsThisWeek ?? 0)}</p>
              </CardContent>
            </Card>
          </StaggerItem>
          <StaggerItem>
            <Card>
              <CardHeader>
                <p className="flex items-center gap-1.5 text-sm font-medium text-muted-foreground">
                  <CalendarCheck className="h-4 w-4" /> Leads to follow up
                </p>
              </CardHeader>
              <CardContent className="pt-3">
                <p className="text-3xl font-semibold tracking-tight">{formatNumber(openLeadsCount ?? 0)}</p>
              </CardContent>
            </Card>
          </StaggerItem>
          <StaggerItem>
            <UsageMeter used={messagesUsed} limit={messagesLimit} title="Messages this month" />
          </StaggerItem>
        </StaggerList>

        <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
          <Card className="lg:col-span-2">
            <CardHeader>
              <p className="text-sm font-medium text-muted-foreground">Recent leads</p>
            </CardHeader>
            <CardContent className="flex flex-col gap-1 pt-3">
              {(recentLeads ?? []).length === 0 ? (
                <p className="py-4 text-sm text-muted-foreground">No leads yet — sync to pull them in.</p>
              ) : (
                (recentLeads ?? []).map((lead) => (
                  <div
                    key={lead.id}
                    className="flex items-center justify-between gap-3 border-b border-border py-2.5 last:border-0"
                  >
                    <div className="min-w-0">
                      <p className="truncate text-sm font-medium text-foreground">{lead.name || lead.phone || "Unknown"}</p>
                      <p className="text-xs text-muted-foreground">{formatDateTime(lead.created_at)}</p>
                    </div>
                    <Badge variant={STATUS_BADGE[lead.status] ?? "outline"}>
                      {STATUS_LABEL[lead.status] ?? lead.status}
                    </Badge>
                  </div>
                ))
              )}
              <Link href="/leads" className="mt-2 text-sm font-medium text-primary hover:underline">
                View all leads →
              </Link>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-muted-foreground">Getting started</p>
                <span className="text-xs text-muted-foreground">
                  {doneCount}/{steps.length}
                </span>
              </div>
            </CardHeader>
            <CardContent className="flex flex-col gap-3 pt-3">
              <ChecklistProgress done={doneCount} total={steps.length} />
              <ul className="flex flex-col gap-2">
                {steps.map((step) => (
                  <li key={step.label}>
                    <Link
                      href={step.href}
                      className="flex items-center gap-2 rounded-lg px-1.5 py-1 text-sm transition-colors hover:bg-muted"
                    >
                      {step.done ? (
                        <CheckCircle2 className="h-4 w-4 shrink-0 text-success" />
                      ) : (
                        <Circle className="h-4 w-4 shrink-0 text-muted-foreground" />
                      )}
                      <span className={step.done ? "text-muted-foreground line-through" : "text-foreground"}>
                        {step.label}
                      </span>
                    </Link>
                  </li>
                ))}
              </ul>
            </CardContent>
          </Card>
        </div>

        <div className="flex flex-wrap gap-2">
          <Link href="/widgets">
            <Button variant="outline" size="sm">
              <LayoutGrid className="h-4 w-4" />
              Create widget
            </Button>
          </Link>
          <Link href="/settings?tab=whatsapp">
            <Button variant="outline" size="sm">
              <MessageCircle className="h-4 w-4" />
              Connect WhatsApp
            </Button>
          </Link>
          <Link href="/campaigns">
            <Button variant="outline" size="sm">
              <Plus className="h-4 w-4" />
              New campaign
            </Button>
          </Link>
        </div>
      </div>
    );
  } catch (err) {
    const message = err instanceof OrgContextError ? err.message : "Couldn't load your dashboard.";
    return (
      <div className="flex flex-col gap-6">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
        </div>
        <Card>
          <CardContent className="pt-5 text-sm text-muted-foreground">{message}</CardContent>
        </Card>
      </div>
    );
  }
}
