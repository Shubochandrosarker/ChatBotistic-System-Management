import { DashboardShell } from "@/components/layout/dashboard-shell";
import { createClient } from "@/lib/supabase/server";
import type { Metadata } from "next";
import { redirect } from "next/navigation";

// Authed app shell — never indexed. The auth gate itself lives below;
// this also protects every route nested under (dashboard) without each
// page needing its own check.
export const metadata: Metadata = {
  robots: { index: false, follow: false, nocache: true },
};

async function resolveOrgName(
  supabase: Awaited<ReturnType<typeof createClient>>,
  userId: string
): Promise<string> {
  try {
    const { data: membership } = await supabase
      .from("org_members")
      .select("org_id")
      .eq("user_id", userId)
      .order("is_primary", { ascending: false })
      .limit(1)
      .maybeSingle();

    const orgId = (membership as { org_id?: string } | null)?.org_id;
    if (!orgId) return "My Organization";

    const { data: org } = await supabase
      .from("organizations")
      .select("name")
      .eq("id", orgId)
      .maybeSingle();

    return (org as { name?: string } | null)?.name || "My Organization";
  } catch {
    // Schema not provisioned yet (fresh env) — shell still renders.
    return "My Organization";
  }
}

export default async function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!user) {
    redirect("/login");
  }

  const orgName = await resolveOrgName(supabase, user.id);

  return (
    <DashboardShell orgName={orgName} userEmail={user.email ?? ""}>
      {children}
    </DashboardShell>
  );
}
