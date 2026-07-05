import { Card, CardContent } from "@/components/ui/card";
import { InboxClient, type ConversationRow, type TeamMember } from "@/components/inbox/inbox-client";
import { requireOrgContext, OrgContextError } from "@/lib/org-context";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { currentPeriodMonth, limitOf } from "@/lib/plans";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Inbox",
};

/**
 * Server component: resolves org context, fetches the org's
 * conversations (+ a handful of supporting reads), and hands
 * everything to a client component that owns the two-pane
 * list/thread UI, live-ish polling, filtering, and sending.
 *
 * Message previews: `conversations` has no denormalized "last message
 * body" column (see 007_whatsapp_provider_and_inbox.sql), so we pull
 * the org's most recent messages once here and reduce them client-side
 * into a conversation_id -> latest body map. Same sizing assumption as
 * `src/app/(dashboard)/leads/page.tsx`'s fetch-all-then-filter note:
 * fine while an org's message volume stays in the low thousands
 * (message_usage/plan quotas already cap monthly send volume well
 * below what would make this expensive); a `LIMIT` keeps the read
 * bounded regardless.
 */
export default async function InboxPage() {
  try {
    const ctx = await requireOrgContext();
    const period = currentPeriodMonth();

    const [
      { data: conversations, error: conversationsError },
      { data: connection },
      { data: usage },
      { data: recentMessages },
      { data: members },
    ] = await Promise.all([
      ctx.supabase
        .from("conversations")
        .select("*")
        .eq("org_id", ctx.org.id)
        .order("last_message_at", { ascending: false, nullsFirst: false }),
      ctx.supabase
        .from("whatsapp_connections")
        .select("provider, status")
        .eq("org_id", ctx.org.id)
        .maybeSingle(),
      ctx.supabase
        .from("message_usage")
        .select("messages_sent")
        .eq("org_id", ctx.org.id)
        .eq("period_month", period)
        .maybeSingle(),
      ctx.supabase
        .from("messages")
        .select("conversation_id, body, direction, created_at")
        .eq("org_id", ctx.org.id)
        .order("created_at", { ascending: false })
        .limit(500),
      ctx.supabase
        .from("org_members")
        .select("id, user_id, role")
        .eq("org_id", ctx.org.id)
        .order("created_at", { ascending: true }),
    ]);

    if (conversationsError) throw new Error(conversationsError.message);

    const previewByConversation: Record<string, string> = {};
    for (const m of recentMessages ?? []) {
      if (!m.conversation_id || previewByConversation[m.conversation_id]) continue;
      previewByConversation[m.conversation_id] = m.body || "";
    }

    // Enrich the roster with display names for the assignee chip/
    // dropdown. Lighter than /api/settings/team's GET (which also
    // fetches email via an `auth.users` admin lookup per member) —
    // the Inbox only needs a name to render, so we skip that round
    // trip and fall back to a shortened user id when no profile name
    // is on file.
    const memberRows = members ?? [];
    const nameByUserId: Record<string, string> = {};
    if (memberRows.length > 0) {
      const admin = supabaseAdmin();
      const { data: profiles } = await admin
        .from("profiles")
        .select("id, full_name")
        .in(
          "id",
          memberRows.map((m) => m.user_id)
        );
      for (const p of profiles ?? []) {
        if (p.full_name) nameByUserId[p.id] = p.full_name;
      }
    }

    const roster: TeamMember[] = memberRows.map((m) => ({
      userId: m.user_id as string,
      name: nameByUserId[m.user_id as string] ?? null,
    }));

    const hasConnectedNumber = connection?.status === "connected" && connection.provider !== "personal";

    return (
      <InboxClient
        orgId={ctx.org.id}
        currentUserId={ctx.userId}
        initialConversations={(conversations ?? []) as ConversationRow[]}
        initialPreviews={previewByConversation}
        roster={roster}
        hasConnectedNumber={hasConnectedNumber}
        initialMessagesUsed={usage?.messages_sent ?? 0}
        messagesLimit={limitOf(ctx.org, "messages_per_month")}
      />
    );
  } catch (err) {
    const message = err instanceof OrgContextError ? err.message : "Couldn't load the inbox.";
    return (
      <div className="flex flex-col gap-6">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Inbox</h1>
        </div>
        <Card>
          <CardContent className="pt-5 text-sm text-muted-foreground">{message}</CardContent>
        </Card>
      </div>
    );
  }
}
