"use client";

import { Badge, type BadgeVariant } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { StaggerItem, StaggerList } from "@/components/motion/stagger-list";
import { formatDateTime } from "@/lib/utils";
import { AlertCircle, Info, Megaphone, Plus, Send } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { CampaignWizard, type AudienceLead } from "./campaign-wizard";
import { UsageMeter } from "./usage-meter";

export interface CampaignRow {
  id: string;
  name: string;
  status: "draft" | "scheduled" | "sending" | "sent";
  message_body: string | null;
  audience: { estimatedCount?: number } | null;
  scheduled_at: string | null;
  sent_count: number;
  created_at: string;
}

const STATUS_BADGE: Record<CampaignRow["status"], BadgeVariant> = {
  draft: "outline",
  scheduled: "warning",
  sending: "default",
  sent: "success",
};

export function CampaignsPageClient({
  orgId,
  campaigns,
  leads,
  widgetNames,
  connectionStatus,
  messagesUsed,
  messagesLimit,
}: {
  orgId: string;
  campaigns: CampaignRow[];
  leads: AudienceLead[];
  widgetNames: Record<string, string>;
  connectionStatus: "connected" | "disconnected";
  messagesUsed: number;
  messagesLimit: number;
}) {
  const router = useRouter();
  const [wizardOpen, setWizardOpen] = useState(false);
  const [sendingId, setSendingId] = useState<string | null>(null);
  const [sendError, setSendError] = useState<string | null>(null);

  // Whether *any* draft/scheduled campaign can actually be fired right
  // now — gates the per-row "Send now" action below. Orgs without a
  // connection or with exhausted quota keep the existing banner/usage
  // guidance instead of a button that would just 400.
  const quotaExhausted = messagesLimit >= 0 && messagesUsed >= messagesLimit;
  const canSendNow = connectionStatus === "connected" && !quotaExhausted;

  async function sendCampaign(id: string) {
    setSendingId(id);
    setSendError(null);
    try {
      const res = await fetch(`/api/campaigns/${id}/send`, { method: "POST" });
      const data = await res.json();
      if (!res.ok) {
        setSendError(data.error || "Failed to send campaign.");
      } else {
        router.refresh();
      }
    } catch (err) {
      setSendError(err instanceof Error ? err.message : "Failed to send campaign.");
    } finally {
      setSendingId(null);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Campaigns</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Broadcast WhatsApp messages to your leads.
          </p>
        </div>
        <Button onClick={() => setWizardOpen(true)}>
          <Plus className="h-4 w-4" />
          New campaign
        </Button>
      </div>

      <UsageMeter used={messagesUsed} limit={messagesLimit} />

      {sendError && (
        <div role="alert" className="flex items-start gap-2 rounded-xl bg-destructive/10 px-3.5 py-2.5 text-sm text-destructive">
          <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
          <span>{sendError}</span>
        </div>
      )}

      {connectionStatus !== "connected" && (
        <Card className="border-warning/40 bg-warning/5">
          <CardContent className="flex items-center gap-3 pt-5 text-sm">
            <Info className="h-4 w-4 shrink-0 text-warning" />
            <span>
              Connect WhatsApp in <span className="font-medium">Settings</span> to send campaigns. You can still
              draft and schedule them now.
            </span>
          </CardContent>
        </Card>
      )}

      {campaigns.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center gap-2 py-10 text-center text-sm text-muted-foreground">
            <Megaphone className="h-8 w-8 text-muted-foreground" />
            <p>No campaigns yet — create your first one.</p>
          </CardContent>
        </Card>
      ) : (
        <StaggerList className="flex flex-col gap-3">
          {campaigns.map((c) => (
            <StaggerItem key={c.id}>
              <Card>
                <CardContent className="flex flex-wrap items-center justify-between gap-3 pt-5">
                  <div className="min-w-0">
                    <div className="flex items-center gap-2">
                      <p className="font-medium text-foreground">{c.name}</p>
                      <Badge variant={STATUS_BADGE[c.status]}>{c.status}</Badge>
                    </div>
                    <p className="mt-1 max-w-md truncate text-sm text-muted-foreground">
                      {c.message_body || "—"}
                    </p>
                  </div>
                  <div className="flex items-center gap-6 text-sm text-muted-foreground">
                    <div>
                      <p className="text-xs uppercase tracking-wide">Audience</p>
                      <p className="text-foreground">{c.audience?.estimatedCount ?? "—"}</p>
                    </div>
                    <div>
                      <p className="text-xs uppercase tracking-wide">Sent</p>
                      <p className="text-foreground">{c.sent_count}</p>
                    </div>
                    {(c.status === "draft" || c.status === "scheduled") && canSendNow && (
                      <Button
                        size="sm"
                        variant="outline"
                        loading={sendingId === c.id}
                        disabled={sendingId !== null && sendingId !== c.id}
                        onClick={() => sendCampaign(c.id)}
                      >
                        <Send className="h-4 w-4" />
                        Send now
                      </Button>
                    )}
                    <div>
                      <p className="text-xs uppercase tracking-wide">
                        {c.status === "scheduled" ? "Scheduled for" : "Created"}
                      </p>
                      <p className="text-foreground">
                        {formatDateTime(c.scheduled_at ?? c.created_at)}
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </StaggerItem>
          ))}
        </StaggerList>
      )}

      <Card>
        <CardContent className="flex items-center gap-3 pt-5 text-sm text-muted-foreground">
          <Info className="h-4 w-4 shrink-0" />
          <span>
            Looking for Tochat campaigns? Tochat.be tracks its own broadcast campaigns separately from this
            page — that data isn&apos;t proxied into the dashboard yet.
          </span>
        </CardContent>
      </Card>

      <CampaignWizard
        open={wizardOpen}
        onOpenChange={setWizardOpen}
        orgId={orgId}
        leads={leads}
        widgetNames={widgetNames}
        connectionStatus={connectionStatus}
        messagesUsed={messagesUsed}
        messagesLimit={messagesLimit}
        onCreated={() => router.refresh()}
      />
    </div>
  );
}
