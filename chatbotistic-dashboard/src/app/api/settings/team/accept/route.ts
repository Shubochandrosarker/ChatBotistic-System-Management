import { NextResponse } from "next/server";
import { createClient } from "@/lib/supabase/server";
import { supabaseAdmin } from "@/lib/supabase/admin";

/**
 * POST /api/settings/team/accept  { token: string }
 *
 * Redeems an org_invites row for the *currently signed-in* user. This
 * intentionally does not use `requireOrgContext()` — the invitee may
 * not have an org membership yet (or belongs to a different org
 * entirely), so there's no existing org context to resolve. It only
 * needs: someone logged in, and a valid, unaccepted invite token.
 *
 * Uses the service-role client throughout because org_members has no
 * INSERT policy for regular users (001/002) — membership is only ever
 * created by the `handle_new_user` trigger or a trusted server path,
 * and this is that trusted server path for invite redemption.
 */
export async function POST(request: Request) {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  let body: { token?: string };
  try {
    body = await request.json();
  } catch {
    return NextResponse.json({ error: "Invalid JSON body." }, { status: 400 });
  }

  const token = (body.token ?? "").trim();
  if (!token) {
    return NextResponse.json({ error: "An invite token is required." }, { status: 400 });
  }

  const admin = supabaseAdmin();
  const { data: invite, error: inviteError } = await admin
    .from("org_invites")
    .select("id, org_id, email, role, accepted")
    .eq("token", token)
    .maybeSingle();

  if (inviteError) {
    return NextResponse.json({ error: inviteError.message }, { status: 500 });
  }
  if (!invite) {
    return NextResponse.json({ error: "That invite link is invalid or has expired." }, { status: 404 });
  }
  if (invite.accepted) {
    return NextResponse.json({ error: "That invite has already been used." }, { status: 409 });
  }

  const { error: memberError } = await admin.from("org_members").insert({
    org_id: invite.org_id,
    user_id: user.id,
    role: invite.role,
    is_primary: false,
  });

  if (memberError) {
    // Most likely a UNIQUE (org_id, user_id) violation — already a member.
    return NextResponse.json(
      { error: memberError.message.includes("duplicate") ? "You're already a member of this organization." : memberError.message },
      { status: 409 }
    );
  }

  await admin.from("org_invites").update({ accepted: true }).eq("id", invite.id);

  return NextResponse.json({ ok: true, orgId: invite.org_id });
}
