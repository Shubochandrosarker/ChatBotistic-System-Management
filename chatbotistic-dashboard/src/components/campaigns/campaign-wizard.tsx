"use client";

import { Button } from "@/components/ui/button";
import { Dialog, DialogBody, DialogFooter, DialogHeader } from "@/components/ui/dialog";
import { Input, Label } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { createClient } from "@/lib/supabase/client";
import { AlertCircle, CheckCircle2, Sparkles } from "lucide-react";
import { useMemo, useState } from "react";

export interface AudienceLead {
  id: string;
  status: string;
  widget_id: string | null;
}

export interface CampaignWizardProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  orgId: string;
  leads: AudienceLead[];
  widgetNames: Record<string, string>;
  connectionStatus: "connected" | "disconnected";
  messagesUsed: number;
  messagesLimit: number; // -1 = unlimited
  onCreated: () => void;
}

type AudienceMode = "all" | "widget" | "status";

const VARIABLE_CHIPS = ["{{name}}", "{{phone}}", "{{widget}}"];

export function CampaignWizard({
  open,
  onOpenChange,
  orgId,
  leads,
  widgetNames,
  connectionStatus,
  messagesUsed,
  messagesLimit,
  onCreated,
}: CampaignWizardProps) {
  const [name, setName] = useState("");
  const [body, setBody] = useState("");
  const [audienceMode, setAudienceMode] = useState<AudienceMode>("all");
  const [audienceWidget, setAudienceWidget] = useState("");
  const [audienceStatus, setAudienceStatus] = useState("open");
  const [sendNow, setSendNow] = useState(true);
  const [scheduledAt, setScheduledAt] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [sendResult, setSendResult] = useState<{ sent: number; failed: number; total: number } | null>(null);

  const widgetIds = useMemo(() => {
    const ids = new Set<string>();
    for (const l of leads) if (l.widget_id) ids.add(l.widget_id);
    return Array.from(ids);
  }, [leads]);

  const audienceCount = useMemo(() => {
    if (audienceMode === "all") return leads.length;
    if (audienceMode === "widget") return leads.filter((l) => l.widget_id === audienceWidget).length;
    return leads.filter((l) => l.status === audienceStatus).length;
  }, [leads, audienceMode, audienceWidget, audienceStatus]);

  const quotaExhausted = messagesLimit >= 0 && messagesUsed >= messagesLimit;
  const canSendNow = connectionStatus === "connected" && !quotaExhausted;

  function insertVariable(token: string) {
    setBody((prev) => `${prev}${prev && !prev.endsWith(" ") ? " " : ""}${token}`);
  }

  function reset() {
    setName("");
    setBody("");
    setAudienceMode("all");
    setAudienceWidget("");
    setAudienceStatus("open");
    setSendNow(true);
    setScheduledAt("");
    setError(null);
    setSendResult(null);
  }

  async function submit(saveAsDraftOnly: boolean) {
    if (!name.trim()) {
      setError("Give the campaign a name.");
      return;
    }
    if (!body.trim()) {
      setError("Write a message body.");
      return;
    }

    setSubmitting(true);
    setError(null);

    const audience = {
      mode: audienceMode,
      widgetId: audienceMode === "widget" ? audienceWidget : null,
      status: audienceMode === "status" ? audienceStatus : null,
      estimatedCount: audienceCount,
    };

    const willSendNow = sendNow && !saveAsDraftOnly && canSendNow;
    const status = saveAsDraftOnly
      ? "draft"
      : sendNow
        ? willSendNow
          ? "sending"
          : "draft"
        : scheduledAt
          ? "scheduled"
          : "draft";

    const supabase = createClient();
    // campaigns has a `FOR ALL` RLS policy scoped to the caller's org
    // (004_campaigns_usage_whatsapp.sql), so this insert can go
    // straight through the browser client.
    const { data: inserted, error: insertError } = await supabase
      .from("campaigns")
      .insert({
        org_id: orgId,
        name: name.trim(),
        status,
        message_body: body.trim(),
        audience,
        scheduled_at: status === "scheduled" && scheduledAt ? new Date(scheduledAt).toISOString() : null,
      })
      .select("id")
      .single();

    if (insertError) {
      setSubmitting(false);
      setError(insertError.message);
      return;
    }

    // `status === "sending"` means the campaign is going out through
    // the org's connected WhatsApp provider right now — actually send
    // it via /api/campaigns/[id]/send, which resolves the audience,
    // checks quota, and calls `increment_message_usage` server-side.
    if (status === "sending" && inserted?.id) {
      setSending(true);
      try {
        const res = await fetch(`/api/campaigns/${inserted.id}/send`, { method: "POST" });
        const data = await res.json();
        if (!res.ok) {
          setError(data.error || "Failed to send campaign.");
        } else {
          setSendResult({ sent: data.sent, failed: data.failed, total: data.total });
        }
      } catch (err) {
        setError(err instanceof Error ? err.message : "Failed to send campaign.");
      } finally {
        setSending(false);
      }
      setSubmitting(false);
      // The campaign row now exists (sent or errored mid-send) —
      // refresh the list, but keep the dialog open so the org can see
      // the sent/failed summary (or the error) before dismissing it.
      onCreated();
      return;
    }

    setSubmitting(false);
    reset();
    onOpenChange(false);
    onCreated();
  }

  function handleOpenChange(nextOpen: boolean) {
    // Don't let a backdrop click/Escape drop the dialog mid-send —
    // the fetch to /api/campaigns/[id]/send is already in flight.
    if (!nextOpen && sending) return;
    if (!nextOpen) reset();
    onOpenChange(nextOpen);
  }

  return (
    <Dialog open={open} onOpenChange={handleOpenChange} className="max-w-xl">
      <DialogHeader onClose={() => handleOpenChange(false)}>New campaign</DialogHeader>
      <DialogBody className="flex flex-col gap-4">
        {error && (
          <div role="alert" className="flex items-start gap-2 rounded-xl bg-destructive/10 px-3.5 py-2.5 text-sm text-destructive">
            <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
            <span>{error}</span>
          </div>
        )}

        {sendResult ? (
          <div className="flex flex-col items-center gap-2 py-6 text-center">
            <CheckCircle2 className="h-10 w-10 text-success" />
            <p className="text-base font-medium text-foreground">Campaign sent</p>
            <p className="text-sm text-muted-foreground">
              {sendResult.sent} of {sendResult.total} message{sendResult.total === 1 ? "" : "s"} sent
              {sendResult.failed > 0 ? ` — ${sendResult.failed} failed` : ""}.
            </p>
          </div>
        ) : (
          <>
            <div>
              <Label htmlFor="campaign-name">Name</Label>
              <Input id="campaign-name" value={name} onChange={(e) => setName(e.target.value)} placeholder="July re-engagement" />
            </div>

            <div>
              <div className="mb-1.5 flex items-center justify-between">
                <Label htmlFor="campaign-body" className="mb-0">
                  Message
                </Label>
                <div className="flex gap-1">
                  {VARIABLE_CHIPS.map((chip) => (
                    <button
                      key={chip}
                      type="button"
                      onClick={() => insertVariable(chip)}
                      className="rounded-full border border-border px-2 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                      {chip}
                    </button>
                  ))}
                </div>
              </div>
              <textarea
                id="campaign-body"
                value={body}
                onChange={(e) => setBody(e.target.value)}
                rows={4}
                placeholder="Hi {{name}}, we've got a summer offer for you…"
                className="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
            </div>

            <div>
              <Label>Audience</Label>
              <div className="flex flex-wrap items-center gap-2">
                <Select value={audienceMode} onChange={(e) => setAudienceMode(e.target.value as AudienceMode)} className="w-40">
                  <option value="all">All leads</option>
                  <option value="widget">By widget</option>
                  <option value="status">By status</option>
                </Select>
                {audienceMode === "widget" && (
                  <Select value={audienceWidget} onChange={(e) => setAudienceWidget(e.target.value)} className="w-48">
                    <option value="">Choose widget…</option>
                    {widgetIds.map((id) => (
                      <option key={id} value={id}>
                        {widgetNames[id] || id}
                      </option>
                    ))}
                  </Select>
                )}
                {audienceMode === "status" && (
                  <Select value={audienceStatus} onChange={(e) => setAudienceStatus(e.target.value)} className="w-40">
                    <option value="open">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="won">Won</option>
                    <option value="lost">Lost</option>
                  </Select>
                )}
                <span className="text-sm text-muted-foreground">≈ {audienceCount} recipients</span>
              </div>
            </div>

            <div>
              <Label>Schedule</Label>
              <div className="flex flex-wrap items-center gap-3">
                <label className="flex items-center gap-1.5 text-sm">
                  <input type="radio" checked={sendNow} onChange={() => setSendNow(true)} />
                  Send now
                </label>
                <label className="flex items-center gap-1.5 text-sm">
                  <input type="radio" checked={!sendNow} onChange={() => setSendNow(false)} />
                  Schedule for later
                </label>
                {!sendNow && (
                  <Input
                    type="datetime-local"
                    value={scheduledAt}
                    onChange={(e) => setScheduledAt(e.target.value)}
                    className="w-56"
                  />
                )}
              </div>
              {sendNow && connectionStatus !== "connected" && (
                <p className="mt-2 text-xs text-warning">
                  No WhatsApp connection yet — this will save as a draft. Connect WhatsApp in Settings to send.
                </p>
              )}
              {sendNow && connectionStatus === "connected" && quotaExhausted && (
                <p className="mt-2 text-xs text-destructive">
                  You&apos;ve used all of this month&apos;s message quota — this will save as a draft. Upgrade your plan to send more.
                </p>
              )}
              {sending && (
                <p className="mt-2 text-xs text-muted-foreground">
                  Sending campaign — this can take a moment for larger audiences…
                </p>
              )}
            </div>
          </>
        )}
      </DialogBody>
      <DialogFooter>
        {sendResult ? (
          <Button
            onClick={() => {
              reset();
              onOpenChange(false);
            }}
          >
            Done
          </Button>
        ) : (
          <>
            <Button variant="ghost" onClick={() => onOpenChange(false)} disabled={sending}>
              Cancel
            </Button>
            <Button variant="outline" loading={submitting && !sending} disabled={sending} onClick={() => submit(true)}>
              Save as draft
            </Button>
            <Button loading={submitting || sending} onClick={() => submit(false)}>
              <Sparkles className="h-4 w-4" />
              {sending ? "Sending…" : sendNow ? (canSendNow ? "Send now" : "Save (can't send yet)") : "Schedule"}
            </Button>
          </>
        )}
      </DialogFooter>
    </Dialog>
  );
}
