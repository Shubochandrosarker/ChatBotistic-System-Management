import { Card, CardContent } from "@/components/ui/card";
import { SettingsTabs } from "@/components/settings/settings-tabs";
import { requireOrgContext, OrgContextError } from "@/lib/org-context";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Settings",
};

export default async function SettingsPage({
  searchParams,
}: {
  // Next.js 16 — route/search params are Promises.
  searchParams: Promise<{ tab?: string }>;
}) {
  const { tab } = await searchParams;

  try {
    const ctx = await requireOrgContext();
    const {
      data: { user },
    } = await ctx.supabase.auth.getUser();

    const [{ data: profile }, { data: connection }, { count: widgetsCount }, { count: seatsCount }, { data: orgFull }] =
      await Promise.all([
        ctx.supabase.from("profiles").select("full_name, avatar_url").eq("id", ctx.userId).maybeSingle(),
        ctx.supabase
          .from("whatsapp_connections")
          .select("provider, phone_number, status")
          .eq("org_id", ctx.org.id)
          .maybeSingle(),
        ctx.supabase
          .from("widgets_cache")
          .select("id", { count: "exact", head: true })
          .eq("org_id", ctx.org.id),
        ctx.supabase
          .from("org_members")
          .select("id", { count: "exact", head: true })
          .eq("org_id", ctx.org.id),
        // `requireOrgContext()` only selects the columns it needs
        // (id, name, plan, entitlements, tochat_user_client) — fetch
        // `white_label` separately rather than modifying that shared
        // helper.
        ctx.supabase.from("organizations").select("white_label").eq("id", ctx.org.id).maybeSingle(),
      ]);

    const { data: membership } = await ctx.supabase
      .from("org_members")
      .select("role")
      .eq("org_id", ctx.org.id)
      .eq("user_id", ctx.userId)
      .maybeSingle();

    return (
      <SettingsTabs
        initialTab={tab}
        userId={ctx.userId}
        userEmail={user?.email ?? ""}
        org={ctx.org}
        role={(membership?.role as "owner" | "admin" | "agent") ?? "agent"}
        profile={profile ?? { full_name: null, avatar_url: null }}
        connection={connection ?? { provider: "personal", phone_number: null, status: "disconnected" }}
        usage={{ widgets: widgetsCount ?? 0, seats: seatsCount ?? 0 }}
        whiteLabel={(orgFull?.white_label as Record<string, unknown> | null) ?? null}
      />
    );
  } catch (err) {
    const message = err instanceof OrgContextError ? err.message : "Couldn't load settings.";
    return (
      <div className="flex flex-col gap-6">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Settings</h1>
        </div>
        <Card>
          <CardContent className="pt-5 text-sm text-muted-foreground">{message}</CardContent>
        </Card>
      </div>
    );
  }
}
