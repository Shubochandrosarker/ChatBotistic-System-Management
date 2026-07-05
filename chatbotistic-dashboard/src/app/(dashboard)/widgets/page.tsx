import { createClient } from "@/lib/supabase/server";
import type { OrgLike } from "@/lib/plans";
import type { WidgetCacheRow } from "@/components/widgets/widget-form-types";
import { WidgetsList } from "@/components/widgets/widgets-list";
import type { Metadata } from "next";
import { redirect } from "next/navigation";

export const metadata: Metadata = {
  title: "Widgets",
};

export default async function WidgetsPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!user) {
    redirect("/login");
  }

  const { data: membership } = await supabase
    .from("org_members")
    .select("org_id")
    .eq("user_id", user.id)
    .order("is_primary", { ascending: false })
    .limit(1)
    .maybeSingle();

  const orgId = (membership as { org_id?: string } | null)?.org_id ?? null;

  let org: OrgLike & { id: string } = { id: "", plan: "free", entitlements: null };
  let widgets: WidgetCacheRow[] = [];

  if (orgId) {
    const { data: orgData } = await supabase
      .from("organizations")
      .select("id, plan, entitlements")
      .eq("id", orgId)
      .maybeSingle();

    if (orgData) {
      org = orgData as OrgLike & { id: string };
    }

    const { data: widgetRows } = await supabase
      .from("widgets_cache")
      .select("*")
      .eq("org_id", orgId)
      .order("synced_at", { ascending: false });

    widgets = (widgetRows as WidgetCacheRow[] | null) ?? [];
  }

  return <WidgetsList org={org} initialWidgets={widgets} />;
}
