import { NextResponse } from "next/server";
import { resolveOrgContext } from "@/lib/org-context";
import { supabaseAdmin } from "@/lib/supabase/admin";

/**
 * GET /api/settings/team
 *
 * Lists the org's members with name + email. This has to go through
 * the service-role client for two reasons:
 *   - `profiles` RLS (002) only lets a user read their OWN profile
 *     row, so a plain session-client join can't show teammates' names.
 *   - Email lives on `auth.users`, which isn't queryable at all from
 *     the browser/session client.
 * `org_members` itself is read via the caller's session client first
 * (RLS already scopes it to the caller's orgs) purely so a non-member
 * can't probe another org's roster by org_id.
 *
 * PATCH /api/settings/team  { memberId, role }
 * DELETE /api/settings/team ?memberId=...
 *   Both require the caller to be an owner/admin of the org — enforced
 *   here since `org_members` has no UPDATE/DELETE policy for members
 *   at all (every non-SELECT write on that table is service-role only).
 */
export async function GET() {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const { data: members, error } = await ctx.supabase
    .from("org_members")
    .select("id, user_id, role, is_primary, created_at")
    .eq("org_id", ctx.org.id)
    .order("created_at", { ascending: true });

  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  const admin = supabaseAdmin();
  const enriched = await Promise.all(
    (members ?? []).map(async (m) => {
      const [{ data: profile }, userRes] = await Promise.all([
        admin.from("profiles").select("full_name, avatar_url").eq("id", m.user_id).maybeSingle(),
        admin.auth.admin.getUserById(m.user_id),
      ]);
      return {
        id: m.id,
        userId: m.user_id,
        role: m.role,
        isPrimary: m.is_primary,
        createdAt: m.created_at,
        name: profile?.full_name ?? null,
        email: userRes.data.user?.email ?? null,
      };
    })
  );

  const { data: invites, error: invitesError } = await ctx.supabase
    .from("org_invites")
    .select("id, email, role, token, accepted, created_at")
    .eq("org_id", ctx.org.id)
    .order("created_at", { ascending: false });

  if (invitesError) {
    return NextResponse.json({ error: invitesError.message }, { status: 500 });
  }

  return NextResponse.json({ members: enriched, invites: invites ?? [] });
}

function isOwnerOrAdmin(role: string | undefined) {
  return role === "owner" || role === "admin";
}

export async function PATCH(request: Request) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const { data: caller } = await ctx.supabase
    .from("org_members")
    .select("role")
    .eq("org_id", ctx.org.id)
    .eq("user_id", ctx.userId)
    .maybeSingle();

  if (!isOwnerOrAdmin(caller?.role)) {
    return NextResponse.json({ error: "Only owners/admins can change roles." }, { status: 403 });
  }

  let body: { memberId?: string; role?: string };
  try {
    body = await request.json();
  } catch {
    return NextResponse.json({ error: "Invalid JSON body." }, { status: 400 });
  }

  if (!body.memberId || (body.role !== "admin" && body.role !== "agent")) {
    return NextResponse.json({ error: "memberId and a valid role are required." }, { status: 400 });
  }

  const admin = supabaseAdmin();
  const { error } = await admin
    .from("org_members")
    .update({ role: body.role })
    .eq("id", body.memberId)
    .eq("org_id", ctx.org.id)
    .neq("role", "owner"); // never demote/reassign the owner via this route

  if (error) return NextResponse.json({ error: error.message }, { status: 500 });
  return NextResponse.json({ ok: true });
}

export async function DELETE(request: Request) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const { data: caller } = await ctx.supabase
    .from("org_members")
    .select("role")
    .eq("org_id", ctx.org.id)
    .eq("user_id", ctx.userId)
    .maybeSingle();

  if (!isOwnerOrAdmin(caller?.role)) {
    return NextResponse.json({ error: "Only owners/admins can remove members." }, { status: 403 });
  }

  const url = new URL(request.url);
  const memberId = url.searchParams.get("memberId");
  if (!memberId) {
    return NextResponse.json({ error: "memberId is required." }, { status: 400 });
  }

  const admin = supabaseAdmin();
  const { error } = await admin
    .from("org_members")
    .delete()
    .eq("id", memberId)
    .eq("org_id", ctx.org.id)
    .neq("role", "owner"); // the owner can't be removed this way

  if (error) return NextResponse.json({ error: error.message }, { status: 500 });
  return NextResponse.json({ ok: true });
}
