"use client";

import { Badge, type BadgeVariant } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { createClient } from "@/lib/supabase/client";
import { cn, formatDateTime } from "@/lib/utils";
import {
  AlertCircle,
  Check,
  CheckCheck,
  Inbox as InboxIcon,
  Search,
  Send,
} from "lucide-react";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";

export type ConversationStatus = "open" | "pending" | "closed";

export interface ConversationRow {
  id: string;
  org_id: string;
  contact_phone: string;
  contact_name: string | null;
  status: ConversationStatus;
  assigned_agent_id: string | null;
  last_message_at: string | null;
  unread_count: number;
  created_at: string;
}

export type MessageStatus = "sent" | "delivered" | "read" | "failed";

export interface MessageRow {
  id: string;
  org_id: string;
  conversation_id: string;
  direction: "inbound" | "outbound";
  body: string | null;
  provider_message_id: string | null;
  status: MessageStatus;
  created_at: string;
}

export interface TeamMember {
  userId: string;
  name: string | null;
}

const STATUS_LABEL: Record<ConversationStatus, string> = {
  open: "Open",
  pending: "Pending",
  closed: "Closed",
};

const STATUS_BADGE: Record<ConversationStatus, BadgeVariant> = {
  open: "default",
  pending: "warning",
  closed: "outline",
};

/** Poll interval for picking up inbound-webhook activity without a full reload. */
const POLL_MS = 12_000;

function memberLabel(member: TeamMember): string {
  return member.name || `${member.userId.slice(0, 8)}…`;
}

function initials(name: string | null, phone: string): string {
  const source = (name || phone || "?").trim();
  const parts = source.split(/\s+/).filter(Boolean);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return source.slice(0, 2).toUpperCase();
}

async function fetchConversations(orgId: string): Promise<ConversationRow[]> {
  // conversations has a `FOR ALL` org-scoped RLS policy
  // (007_whatsapp_provider_and_inbox.sql), so reads/writes here go
  // straight through the browser client — no API route needed, same
  // pattern as src/components/leads/leads-page-client.tsx.
  const supabase = createClient();
  const { data, error } = await supabase
    .from("conversations")
    .select("*")
    .eq("org_id", orgId)
    .order("last_message_at", { ascending: false, nullsFirst: false });
  if (error) throw new Error(error.message);
  return (data ?? []) as ConversationRow[];
}

async function fetchMessages(conversationId: string): Promise<MessageRow[]> {
  const supabase = createClient();
  const { data, error } = await supabase
    .from("messages")
    .select("*")
    .eq("conversation_id", conversationId)
    .order("created_at", { ascending: true });
  if (error) throw new Error(error.message);
  return (data ?? []) as MessageRow[];
}

/** Delivery-status icon for an outbound message bubble. */
function DeliveryIcon({ status }: { status: MessageStatus }) {
  if (status === "failed") return <AlertCircle className="h-3.5 w-3.5 text-destructive" />;
  if (status === "read") return <CheckCheck className="h-3.5 w-3.5" />;
  if (status === "delivered") return <CheckCheck className="h-3.5 w-3.5 opacity-70" />;
  return <Check className="h-3.5 w-3.5 opacity-70" />;
}

/**
 * Shared team Inbox — two-pane conversation list + thread, built on
 * the `conversations`/`messages` schema from
 * `supabase/migrations/007_whatsapp_provider_and_inbox.sql`.
 *
 * Reads/status/assignment updates go straight through the browser
 * Supabase client (RLS already scopes both tables to the caller's
 * org). Sending is the one write that has to go through a server
 * route (`POST /api/inbox/send`) — it calls out to the org's WhatsApp
 * provider and increments the shared message-usage counter, neither
 * of which the browser client can do safely.
 */
export function InboxClient({
  orgId,
  currentUserId,
  initialConversations,
  initialPreviews,
  roster,
  hasConnectedNumber,
  initialMessagesUsed,
  messagesLimit,
}: {
  orgId: string;
  currentUserId: string;
  initialConversations: ConversationRow[];
  initialPreviews: Record<string, string>;
  roster: TeamMember[];
  hasConnectedNumber: boolean;
  initialMessagesUsed: number;
  messagesLimit: number;
}) {
  const [conversations, setConversations] = useState<ConversationRow[]>(initialConversations);
  const [previews, setPreviews] = useState<Record<string, string>>(initialPreviews);
  const [selectedId, setSelectedId] = useState<string | null>(initialConversations[0]?.id ?? null);
  const [messages, setMessages] = useState<MessageRow[]>([]);
  const [loadingThread, setLoadingThread] = useState(false);

  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState<"all" | ConversationStatus>("all");
  const [agentFilter, setAgentFilter] = useState<string>("all");

  const [composerValue, setComposerValue] = useState("");
  const [sending, setSending] = useState(false);
  const [sendError, setSendError] = useState<string | null>(null);
  const [messagesUsed, setMessagesUsed] = useState(initialMessagesUsed);

  const [pendingStatusId, setPendingStatusId] = useState<string | null>(null);
  const [pendingAssignId, setPendingAssignId] = useState<string | null>(null);

  const threadEndRef = useRef<HTMLDivElement>(null);
  const selectedIdRef = useRef(selectedId);
  selectedIdRef.current = selectedId;

  const rosterByUserId = useMemo(() => {
    const map: Record<string, TeamMember> = {};
    for (const m of roster) map[m.userId] = m;
    return map;
  }, [roster]);

  const selected = useMemo(
    () => conversations.find((c) => c.id === selectedId) ?? null,
    [conversations, selectedId]
  );

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase();
    return conversations.filter((c) => {
      if (statusFilter !== "all" && c.status !== statusFilter) return false;
      if (agentFilter === "unassigned" && c.assigned_agent_id) return false;
      if (agentFilter !== "all" && agentFilter !== "unassigned" && c.assigned_agent_id !== agentFilter)
        return false;
      if (q) {
        const haystack = `${c.contact_name ?? ""} ${c.contact_phone}`.toLowerCase();
        if (!haystack.includes(q)) return false;
      }
      return true;
    });
  }, [conversations, search, statusFilter, agentFilter]);

  const quotaExhausted = messagesLimit >= 0 && messagesUsed >= messagesLimit;

  // Load the thread + mark-as-read whenever the selected conversation changes.
  useEffect(() => {
    if (!selectedId) {
      setMessages([]);
      return;
    }
    let cancelled = false;
    setLoadingThread(true);
    fetchMessages(selectedId)
      .then((rows) => {
        if (!cancelled) setMessages(rows);
      })
      .catch(() => {
        if (!cancelled) setMessages([]);
      })
      .finally(() => {
        if (!cancelled) setLoadingThread(false);
      });

    setConversations((prev) => {
      const conversation = prev.find((c) => c.id === selectedId);
      if (!conversation || conversation.unread_count === 0) return prev;
      // Fire-and-forget: reset unread_count in the DB to match what we
      // just optimistically set locally.
      const supabase = createClient();
      void supabase.from("conversations").update({ unread_count: 0 }).eq("id", selectedId).eq("org_id", orgId);
      return prev.map((c) => (c.id === selectedId ? { ...c, unread_count: 0 } : c));
    });

    return () => {
      cancelled = true;
    };
  }, [selectedId, orgId]);

  useEffect(() => {
    threadEndRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages.length]);

  // Light polling so inbound webhook activity shows up without a full
  // page reload. Always refreshes the conversation list; additionally
  // refreshes the open thread (which also keeps that one
  // conversation's preview current — other conversations' previews
  // only refresh on next page load, see the preview-map comment in
  // src/app/(dashboard)/inbox/page.tsx).
  useEffect(() => {
    const interval = setInterval(async () => {
      try {
        const rows = await fetchConversations(orgId);
        setConversations(rows);
      } catch {
        // best-effort — leave the current list in place on failure
      }
      const currentSelected = selectedIdRef.current;
      if (currentSelected) {
        try {
          const rows = await fetchMessages(currentSelected);
          setMessages(rows);
          const last = rows[rows.length - 1];
          if (last) setPreviews((prev) => ({ ...prev, [currentSelected]: last.body || "" }));
        } catch {
          // best-effort
        }
      }
    }, POLL_MS);
    return () => clearInterval(interval);
  }, [orgId]);

  async function updateStatus(id: string, status: ConversationStatus) {
    setPendingStatusId(id);
    const supabase = createClient();
    const { error } = await supabase.from("conversations").update({ status }).eq("id", id).eq("org_id", orgId);
    if (!error) {
      setConversations((prev) => prev.map((c) => (c.id === id ? { ...c, status } : c)));
    }
    setPendingStatusId(null);
  }

  async function assignAgent(id: string, agentId: string | null) {
    setPendingAssignId(id);
    const supabase = createClient();
    const { error } = await supabase
      .from("conversations")
      .update({ assigned_agent_id: agentId })
      .eq("id", id)
      .eq("org_id", orgId);
    if (!error) {
      setConversations((prev) => prev.map((c) => (c.id === id ? { ...c, assigned_agent_id: agentId } : c)));
    }
    setPendingAssignId(null);
  }

  const sendMessage = useCallback(async () => {
    if (!selected) return;
    const body = composerValue.trim();
    if (!body) return;

    setSending(true);
    setSendError(null);
    try {
      const res = await fetch("/api/inbox/send", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ conversationId: selected.id, body }),
      });
      const data = await res.json();
      if (!res.ok) {
        setSendError(data.error || "Failed to send message.");
        return;
      }

      const inserted = data.message as MessageRow;
      setMessages((prev) => [...prev, inserted]);
      setPreviews((prev) => ({ ...prev, [selected.id]: inserted.body || "" }));
      setConversations((prev) =>
        prev
          .map((c) => (c.id === selected.id ? { ...c, last_message_at: inserted.created_at } : c))
          .sort((a, b) => {
            const at = a.last_message_at ? new Date(a.last_message_at).getTime() : 0;
            const bt = b.last_message_at ? new Date(b.last_message_at).getTime() : 0;
            return bt - at;
          })
      );
      if (typeof data.messagesUsed === "number") setMessagesUsed(data.messagesUsed);
      setComposerValue("");
    } catch {
      setSendError("Failed to send message — check your connection.");
    } finally {
      setSending(false);
    }
  }, [selected, composerValue]);

  if (conversations.length === 0) {
    return (
      <div className="flex flex-col gap-6">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Inbox</h1>
        </div>
        <Card>
          <CardContent className="flex flex-col items-center gap-3 py-16 text-center">
            <InboxIcon className="h-10 w-10 text-muted-foreground" />
            <p className="text-sm text-muted-foreground">
              {hasConnectedNumber
                ? "No conversations yet — inbound WhatsApp messages will show up here."
                : "Connect your own WhatsApp number in Settings to start receiving messages here."}
            </p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Inbox</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            {filtered.length} of {conversations.length} conversations
          </p>
        </div>
      </div>

      <div className="grid flex-1 grid-cols-1 gap-4 lg:grid-cols-[360px_1fr]">
        {/* Conversation list */}
        <Card className="flex max-h-[calc(100vh-220px)] min-h-[480px] flex-col overflow-hidden">
          <div className="flex flex-col gap-2 border-b border-border p-3">
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search name or phone…"
                className="pl-9"
              />
            </div>
            <div className="flex gap-2">
              <Select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value as "all" | ConversationStatus)}
                className="flex-1"
              >
                <option value="all">All statuses</option>
                <option value="open">Open</option>
                <option value="pending">Pending</option>
                <option value="closed">Closed</option>
              </Select>
              <Select value={agentFilter} onChange={(e) => setAgentFilter(e.target.value)} className="flex-1">
                <option value="all">All agents</option>
                <option value="unassigned">Unassigned</option>
                {roster.map((m) => (
                  <option key={m.userId} value={m.userId}>
                    {memberLabel(m)}
                  </option>
                ))}
              </Select>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto">
            {filtered.length === 0 ? (
              <p className="p-5 text-sm text-muted-foreground">No conversations match these filters.</p>
            ) : (
              filtered.map((c) => {
                const assignee = c.assigned_agent_id ? rosterByUserId[c.assigned_agent_id] : null;
                return (
                  <button
                    key={c.id}
                    type="button"
                    onClick={() => setSelectedId(c.id)}
                    className={cn(
                      "flex w-full items-start gap-3 border-b border-border px-4 py-3 text-left transition-colors hover:bg-muted/50",
                      selectedId === c.id && "bg-accent"
                    )}
                  >
                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold text-foreground">
                      {initials(c.contact_name, c.contact_phone)}
                    </span>
                    <span className="min-w-0 flex-1">
                      <span className="flex items-center justify-between gap-2">
                        <span className="truncate text-sm font-medium text-foreground">
                          {c.contact_name || c.contact_phone}
                        </span>
                        <span className="shrink-0 text-xs text-muted-foreground">
                          {c.last_message_at ? formatDateTime(c.last_message_at) : ""}
                        </span>
                      </span>
                      <span className="mt-0.5 flex items-center gap-1.5">
                        <Badge variant={STATUS_BADGE[c.status]} className="shrink-0">
                          {STATUS_LABEL[c.status]}
                        </Badge>
                        {assignee && (
                          <span className="truncate text-xs text-muted-foreground">{memberLabel(assignee)}</span>
                        )}
                      </span>
                      <span className="mt-0.5 flex items-center justify-between gap-2">
                        <span className="truncate text-xs text-muted-foreground">
                          {previews[c.id] || "No messages yet"}
                        </span>
                        {c.unread_count > 0 && (
                          <Badge className="shrink-0 bg-primary text-primary-foreground">{c.unread_count}</Badge>
                        )}
                      </span>
                    </span>
                  </button>
                );
              })
            )}
          </div>
        </Card>

        {/* Thread */}
        <Card className="flex max-h-[calc(100vh-220px)] min-h-[480px] flex-col overflow-hidden">
          {!selected ? (
            <div className="flex flex-1 items-center justify-center p-8 text-sm text-muted-foreground">
              Select a conversation to view the thread.
            </div>
          ) : (
            <>
              <div className="flex flex-wrap items-center justify-between gap-2 border-b border-border p-3">
                <div className="flex items-center gap-2">
                  <span className="flex h-9 w-9 items-center justify-center rounded-full bg-muted text-xs font-semibold text-foreground">
                    {initials(selected.contact_name, selected.contact_phone)}
                  </span>
                  <div>
                    <p className="text-sm font-semibold text-foreground">
                      {selected.contact_name || selected.contact_phone}
                    </p>
                    <p className="text-xs text-muted-foreground">{selected.contact_phone}</p>
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <Select
                    value={selected.status}
                    disabled={pendingStatusId === selected.id}
                    onChange={(e) => updateStatus(selected.id, e.target.value as ConversationStatus)}
                    className="w-32"
                  >
                    <option value="open">Open</option>
                    <option value="pending">Pending</option>
                    <option value="closed">Closed</option>
                  </Select>
                  <Select
                    value={selected.assigned_agent_id ?? ""}
                    disabled={pendingAssignId === selected.id}
                    onChange={(e) => assignAgent(selected.id, e.target.value || null)}
                    className="w-40"
                  >
                    <option value="">Unassigned</option>
                    {roster.map((m) => (
                      <option key={m.userId} value={m.userId}>
                        {m.userId === currentUserId ? "You" : memberLabel(m)}
                      </option>
                    ))}
                  </Select>
                </div>
              </div>

              <div className="flex-1 space-y-3 overflow-y-auto p-4">
                {loadingThread ? (
                  <p className="text-sm text-muted-foreground">Loading…</p>
                ) : messages.length === 0 ? (
                  <p className="text-sm text-muted-foreground">No messages in this conversation yet.</p>
                ) : (
                  messages.map((m) => (
                    <div
                      key={m.id}
                      className={cn("flex", m.direction === "outbound" ? "justify-end" : "justify-start")}
                    >
                      <div
                        className={cn(
                          "max-w-[75%] rounded-2xl px-3.5 py-2 text-sm shadow-soft",
                          m.direction === "outbound"
                            ? "bg-whatsapp text-primary-foreground"
                            : "bg-muted text-foreground"
                        )}
                      >
                        <p className="whitespace-pre-wrap break-words">{m.body || "—"}</p>
                        <div
                          className={cn(
                            "mt-1 flex items-center gap-1 text-[11px]",
                            m.direction === "outbound" ? "text-primary-foreground/70" : "text-muted-foreground"
                          )}
                        >
                          <span>{formatDateTime(m.created_at)}</span>
                          {m.direction === "outbound" && <DeliveryIcon status={m.status} />}
                        </div>
                      </div>
                    </div>
                  ))
                )}
                <div ref={threadEndRef} />
              </div>

              <div className="border-t border-border p-3">
                {!hasConnectedNumber ? (
                  <p className="rounded-xl bg-warning/15 px-3 py-2 text-xs text-warning">
                    Connect WhatsApp in Settings to reply.
                  </p>
                ) : quotaExhausted ? (
                  <p className="rounded-xl bg-destructive/10 px-3 py-2 text-xs text-destructive">
                    You&apos;ve used all of this month&apos;s message quota — upgrade your plan to keep sending.
                  </p>
                ) : (
                  <>
                    {sendError && (
                      <p className="mb-2 flex items-center gap-1.5 text-xs text-destructive">
                        <AlertCircle className="h-3.5 w-3.5 shrink-0" />
                        {sendError}
                      </p>
                    )}
                    <div className="flex items-end gap-2">
                      <textarea
                        value={composerValue}
                        onChange={(e) => setComposerValue(e.target.value)}
                        onKeyDown={(e) => {
                          if (e.key === "Enter" && !e.shiftKey) {
                            e.preventDefault();
                            sendMessage();
                          }
                        }}
                        disabled={sending}
                        rows={2}
                        placeholder="Write a reply…"
                        className="flex-1 resize-none rounded-xl border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50"
                      />
                      <Button
                        size="icon"
                        onClick={sendMessage}
                        loading={sending}
                        disabled={!composerValue.trim()}
                        aria-label="Send message"
                      >
                        <Send className="h-4 w-4" />
                      </Button>
                    </div>
                  </>
                )}
              </div>
            </>
          )}
        </Card>
      </div>
    </div>
  );
}
