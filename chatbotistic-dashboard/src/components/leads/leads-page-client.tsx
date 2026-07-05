"use client";

import { Badge, type BadgeVariant } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Dialog, DialogBody, DialogHeader } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { Switch } from "@/components/ui/switch";
import { StaggerItem, StaggerList } from "@/components/motion/stagger-list";
import { createClient } from "@/lib/supabase/client";
import { cn, formatDateTime, waDigits } from "@/lib/utils";
import {
  Download,
  ExternalLink,
  MessageSquare,
  RefreshCw,
  Search,
  X,
} from "lucide-react";
import { useRouter } from "next/navigation";
import { useMemo, useState } from "react";

export type LeadStatus = "open" | "contacted" | "won" | "lost";

export interface LeadRow {
  id: string;
  org_id: string;
  tochat_stat_id: string;
  phone: string | null;
  name: string | null;
  country: string | null;
  referer: string | null;
  widget_id: string | null;
  agent: string | null;
  fields: Record<string, unknown> | null;
  booking_data: Record<string, unknown> | null;
  status: LeadStatus;
  created_at: string;
}

const STATUS_LABEL: Record<LeadStatus, string> = {
  open: "New",
  contacted: "Contacted",
  won: "Won",
  lost: "Lost",
};

const STATUS_BADGE: Record<LeadStatus, BadgeVariant> = {
  open: "outline",
  contacted: "warning",
  won: "success",
  lost: "destructive",
};

const NEXT_STATUS: Record<LeadStatus, { status: LeadStatus; label: string }[]> = {
  open: [
    { status: "contacted", label: "Mark contacted" },
    { status: "lost", label: "Mark lost" },
  ],
  contacted: [
    { status: "won", label: "Mark won" },
    { status: "lost", label: "Mark lost" },
  ],
  won: [{ status: "open", label: "Reopen" }],
  lost: [{ status: "open", label: "Reopen" }],
};

function csvEscape(value: unknown): string {
  const s = value === null || value === undefined ? "" : String(value);
  if (/[",\n]/.test(s)) return `"${s.replace(/"/g, '""')}"`;
  return s;
}

function toCsv(rows: LeadRow[], widgetNames: Record<string, string>): string {
  const header = [
    "Name",
    "Phone",
    "Status",
    "Widget",
    "Country",
    "Referer",
    "Created",
    "Fields",
  ];
  const lines = rows.map((r) =>
    [
      r.name ?? "",
      r.phone ?? "",
      STATUS_LABEL[r.status],
      (r.widget_id && widgetNames[r.widget_id]) || r.widget_id || "",
      r.country ?? "",
      r.referer ?? "",
      r.created_at,
      r.fields ? JSON.stringify(r.fields) : "",
    ]
      .map(csvEscape)
      .join(",")
  );
  return [header.join(","), ...lines].join("\n");
}

function downloadCsv(csv: string, filename: string) {
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

export function LeadsPageClient({
  initialLeads,
  widgetNames,
}: {
  initialLeads: LeadRow[];
  widgetNames: Record<string, string>;
}) {
  const router = useRouter();
  const [leads, setLeads] = useState<LeadRow[]>(initialLeads);
  const [search, setSearch] = useState("");
  const [widgetFilter, setWidgetFilter] = useState("all");
  const [statusFilter, setStatusFilter] = useState<"all" | LeadStatus>("all");
  const [newOnly, setNewOnly] = useState(false);
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [selected, setSelected] = useState<LeadRow | null>(null);
  const [syncing, setSyncing] = useState(false);
  const [syncMessage, setSyncMessage] = useState<string | null>(null);
  const [pendingId, setPendingId] = useState<string | null>(null);

  const widgetOptions = useMemo(() => {
    const ids = new Set<string>();
    for (const l of leads) if (l.widget_id) ids.add(l.widget_id);
    return Array.from(ids);
  }, [leads]);

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase();
    const from = dateFrom ? new Date(dateFrom).getTime() : null;
    const to = dateTo ? new Date(dateTo).getTime() + 86_400_000 - 1 : null;

    return leads.filter((lead) => {
      if (newOnly && lead.status !== "open") return false;
      if (!newOnly && statusFilter !== "all" && lead.status !== statusFilter) return false;
      if (widgetFilter !== "all" && lead.widget_id !== widgetFilter) return false;

      if (q) {
        const haystack = `${lead.name ?? ""} ${lead.phone ?? ""}`.toLowerCase();
        if (!haystack.includes(q)) return false;
      }

      if (from !== null || to !== null) {
        const created = new Date(lead.created_at).getTime();
        if (from !== null && created < from) return false;
        if (to !== null && created > to) return false;
      }

      return true;
    });
  }, [leads, search, widgetFilter, statusFilter, newOnly, dateFrom, dateTo]);

  async function updateStatus(id: string, status: LeadStatus) {
    setPendingId(id);
    const supabase = createClient();
    // leads has a `FOR ALL` RLS policy scoped to the caller's org
    // (supabase/migrations/003_widgets_leads_landing.sql), so a plain
    // status update can go straight through the browser client — no
    // API route needed for this simple case.
    const { error } = await supabase.from("leads").update({ status }).eq("id", id);
    if (!error) {
      setLeads((prev) => prev.map((l) => (l.id === id ? { ...l, status } : l)));
      setSelected((prev) => (prev && prev.id === id ? { ...prev, status } : prev));
    }
    setPendingId(null);
  }

  async function syncNow() {
    setSyncing(true);
    setSyncMessage(null);
    try {
      const res = await fetch("/api/leads/sync", { method: "POST" });
      const data = await res.json();
      if (!res.ok) {
        setSyncMessage(data.error || "Sync failed.");
      } else {
        setSyncMessage(`Synced ${data.synced} of ${data.total} leads.`);
        router.refresh();
      }
    } catch {
      setSyncMessage("Sync failed — check your connection.");
    } finally {
      setSyncing(false);
    }
  }

  function exportCsv() {
    downloadCsv(toCsv(filtered, widgetNames), `leads-${new Date().toISOString().slice(0, 10)}.csv`);
  }

  const hasActiveFilters =
    search || widgetFilter !== "all" || statusFilter !== "all" || newOnly || dateFrom || dateTo;

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Leads</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            {filtered.length} of {leads.length} leads
          </p>
        </div>
        <div className="flex items-center gap-2">
          {syncMessage && <span className="text-xs text-muted-foreground">{syncMessage}</span>}
          <Button variant="outline" size="sm" onClick={exportCsv} disabled={filtered.length === 0}>
            <Download className="h-4 w-4" />
            Export CSV
          </Button>
          <Button size="sm" onClick={syncNow} loading={syncing}>
            <RefreshCw className={cn("h-4 w-4", syncing && "animate-spin")} />
            Sync now
          </Button>
        </div>
      </div>

      <Card>
        <CardContent className="flex flex-wrap items-end gap-3 pt-5">
          <div className="min-w-[200px] flex-1">
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search name or phone…"
                className="pl-9"
              />
            </div>
          </div>

          <div className="w-40">
            <Select value={widgetFilter} onChange={(e) => setWidgetFilter(e.target.value)}>
              <option value="all">All widgets</option>
              {widgetOptions.map((id) => (
                <option key={id} value={id}>
                  {widgetNames[id] || id}
                </option>
              ))}
            </Select>
          </div>

          <div className="w-40">
            <Select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value as "all" | LeadStatus)}
              disabled={newOnly}
            >
              <option value="all">All statuses</option>
              <option value="open">New</option>
              <option value="contacted">Contacted</option>
              <option value="won">Won</option>
              <option value="lost">Lost</option>
            </Select>
          </div>

          <div className="flex items-center gap-2">
            <Input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="w-36" />
            <span className="text-sm text-muted-foreground">to</span>
            <Input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="w-36" />
          </div>

          <label className="flex items-center gap-2 text-sm text-foreground">
            <Switch checked={newOnly} onCheckedChange={setNewOnly} aria-label="New leads only" />
            New only
          </label>

          {hasActiveFilters && (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => {
                setSearch("");
                setWidgetFilter("all");
                setStatusFilter("all");
                setNewOnly(false);
                setDateFrom("");
                setDateTo("");
              }}
            >
              <X className="h-3.5 w-3.5" />
              Clear
            </Button>
          )}
        </CardContent>
      </Card>

      {filtered.length === 0 ? (
        <Card>
          <CardContent className="pt-5 text-sm text-muted-foreground">
            No leads match these filters yet.
          </CardContent>
        </Card>
      ) : (
        <Card className="overflow-hidden">
          <div className="overflow-x-auto">
            {/* Div-based grid "table" (not a real <table>) so each row
                can be a StaggerItem — framer-motion needs a normal
                element to animate, and a <div> can't legally sit
                between <table>/<tbody> without the browser hoisting it
                out and breaking the layout. */}
            <div className="min-w-[760px]">
              <div className="grid grid-cols-[2fr_2fr_1.2fr_1.4fr_1fr_0.9fr] gap-2 border-b border-border px-5 py-3 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                <span>Lead</span>
                <span>Status</span>
                <span>Widget</span>
                <span>Referer</span>
                <span>Created</span>
                <span className="text-right">Chat</span>
              </div>
              <StaggerList className="flex flex-col">
                {filtered.map((lead) => (
                  <StaggerItem key={lead.id}>
                    <div
                      role="button"
                      tabIndex={0}
                      onClick={() => setSelected(lead)}
                      onKeyDown={(e) => e.key === "Enter" && setSelected(lead)}
                      className="grid cursor-pointer grid-cols-[2fr_2fr_1.2fr_1.4fr_1fr_0.9fr] items-center gap-2 border-b border-border px-5 py-3 text-sm last:border-0 hover:bg-muted/50"
                    >
                      <div>
                        <div className="font-medium text-foreground">{lead.name || "—"}</div>
                        <div className="text-xs text-muted-foreground">{lead.phone || "—"}</div>
                      </div>
                      <div
                        className="flex flex-wrap items-center gap-1.5"
                        onClick={(e) => e.stopPropagation()}
                      >
                        <Badge variant={STATUS_BADGE[lead.status]}>{STATUS_LABEL[lead.status]}</Badge>
                        {NEXT_STATUS[lead.status].map((next) => (
                          <button
                            key={next.status}
                            type="button"
                            disabled={pendingId === lead.id}
                            onClick={() => updateStatus(lead.id, next.status)}
                            className="rounded-full border border-border px-2 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground disabled:opacity-50"
                          >
                            {next.label}
                          </button>
                        ))}
                      </div>
                      <div className="truncate text-muted-foreground">
                        {(lead.widget_id && widgetNames[lead.widget_id]) || lead.widget_id || "—"}
                      </div>
                      <div className="truncate text-muted-foreground">{lead.referer || "—"}</div>
                      <div className="text-muted-foreground">{formatDateTime(lead.created_at)}</div>
                      <div className="text-right">
                        {lead.phone && (
                          <a
                            href={`https://wa.me/${waDigits(lead.phone)}`}
                            target="_blank"
                            rel="noreferrer"
                            onClick={(e) => e.stopPropagation()}
                            className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                          >
                            <MessageSquare className="h-3.5 w-3.5" />
                            Chat
                          </a>
                        )}
                      </div>
                    </div>
                  </StaggerItem>
                ))}
              </StaggerList>
            </div>
          </div>
        </Card>
      )}

      <Dialog open={!!selected} onOpenChange={(open) => !open && setSelected(null)}>
        {selected && (
          <>
            <DialogHeader onClose={() => setSelected(null)}>{selected.name || "Lead detail"}</DialogHeader>
            <DialogBody className="flex flex-col gap-4">
              <div className="flex flex-wrap items-center gap-2">
                <Badge variant={STATUS_BADGE[selected.status]}>{STATUS_LABEL[selected.status]}</Badge>
                {NEXT_STATUS[selected.status].map((next) => (
                  <Button
                    key={next.status}
                    variant="outline"
                    size="sm"
                    disabled={pendingId === selected.id}
                    onClick={() => updateStatus(selected.id, next.status)}
                  >
                    {next.label}
                  </Button>
                ))}
                {selected.phone && (
                  <a
                    href={`https://wa.me/${waDigits(selected.phone)}`}
                    target="_blank"
                    rel="noreferrer"
                    className="ml-auto inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
                  >
                    Open WhatsApp chat
                    <ExternalLink className="h-3.5 w-3.5" />
                  </a>
                )}
              </div>

              <dl className="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt className="text-muted-foreground">Phone</dt>
                <dd>{selected.phone || "—"}</dd>
                <dt className="text-muted-foreground">Country</dt>
                <dd>{selected.country || "—"}</dd>
                <dt className="text-muted-foreground">Widget</dt>
                <dd>{(selected.widget_id && widgetNames[selected.widget_id]) || selected.widget_id || "—"}</dd>
                <dt className="text-muted-foreground">Agent</dt>
                <dd>{selected.agent || "—"}</dd>
                <dt className="text-muted-foreground">Referer</dt>
                <dd className="truncate">{selected.referer || "—"}</dd>
                <dt className="text-muted-foreground">Created</dt>
                <dd>{formatDateTime(selected.created_at)}</dd>
              </dl>

              {selected.fields && Object.keys(selected.fields).length > 0 && (
                <div>
                  <p className="mb-1.5 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    Form fields
                  </p>
                  <dl className="grid grid-cols-2 gap-x-4 gap-y-2 rounded-xl bg-muted/50 p-3 text-sm">
                    {Object.entries(selected.fields).map(([key, value]) => (
                      <div key={key} className="contents">
                        <dt className="text-muted-foreground">{key}</dt>
                        <dd className="truncate">{String(value)}</dd>
                      </div>
                    ))}
                  </dl>
                </div>
              )}

              {selected.booking_data && Object.keys(selected.booking_data).length > 0 && (
                <div>
                  <p className="mb-1.5 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    Booking
                  </p>
                  <dl className="grid grid-cols-2 gap-x-4 gap-y-2 rounded-xl bg-muted/50 p-3 text-sm">
                    {Object.entries(selected.booking_data).map(([key, value]) => (
                      <div key={key} className="contents">
                        <dt className="text-muted-foreground">{key}</dt>
                        <dd className="truncate">{String(value)}</dd>
                      </div>
                    ))}
                  </dl>
                </div>
              )}
            </DialogBody>
          </>
        )}
      </Dialog>
    </div>
  );
}
