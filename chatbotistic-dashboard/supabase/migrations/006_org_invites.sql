-- ============================================================
-- 006 — Org invites (Team tab, Settings)
--
-- There is no invite-token/email-sending infrastructure in this app
-- yet, so this table is the minimal shape needed to support "invite
-- by email" honestly: an owner/admin creates a pending invite row
-- (with a random token), shares the link manually (no mailer wired
-- up — see src/components/settings/team-tab.tsx), and the invitee
-- (once they have *any* Supabase auth account — sign up first via the
-- normal /signup flow) pastes the token into Settings > Team > "Accept
-- an invite" to redeem it via POST /api/settings/team/accept, which
-- uses the service-role client to insert into org_members (that table
-- intentionally has no INSERT/UPDATE policy for regular users — see
-- 001/002 — so redemption must go through a server route).
--
-- RLS below only needs to cover owner/admin management (list/create/
-- revoke via the browser client); the accept path bypasses RLS
-- entirely via the service-role client since the invitee is not yet
-- an org member and couldn't otherwise read their own invite by
-- org-scoped policy.
--
-- Idempotent — safe to run multiple times.
-- ============================================================

CREATE TABLE IF NOT EXISTS org_invites (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  email TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'agent'
    CHECK (role IN ('admin', 'agent')),
  token TEXT NOT NULL UNIQUE,
  accepted BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (org_id, email)
);

CREATE INDEX IF NOT EXISTS idx_org_invites_org ON org_invites(org_id);
CREATE INDEX IF NOT EXISTS idx_org_invites_token ON org_invites(token);

ALTER TABLE org_invites ENABLE ROW LEVEL SECURITY;

-- Owners/admins of the target org can view, create, and revoke
-- invites for their own org via the browser client. Deliberately
-- queries org_members directly (not the user_org_ids() helper from
-- 002) since the role check (owner/admin only) is specific to this
-- policy.
DROP POLICY IF EXISTS "Owners and admins manage org_invites" ON org_invites;
CREATE POLICY "Owners and admins manage org_invites" ON org_invites FOR ALL
  USING (
    org_id IN (
      SELECT org_id FROM org_members
      WHERE user_id = auth.uid() AND role IN ('owner', 'admin')
    )
  )
  WITH CHECK (
    org_id IN (
      SELECT org_id FROM org_members
      WHERE user_id = auth.uid() AND role IN ('owner', 'admin')
    )
  );
