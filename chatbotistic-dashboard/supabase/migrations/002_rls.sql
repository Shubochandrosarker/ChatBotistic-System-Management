-- ============================================================
-- 002 — RLS for organizations / org_members / profiles
--
-- `user_org_ids()` is SECURITY DEFINER so it bypasses RLS on
-- org_members when evaluated from another table's policy — without
-- that, an org_members policy that queries org_members itself would
-- recurse. Every later migration's org-scoped tables reuse this
-- helper (see 003/004/005).
--
-- Idempotent — safe to run multiple times.
-- ============================================================

CREATE OR REPLACE FUNCTION public.user_org_ids()
RETURNS SETOF UUID
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
  SELECT org_id FROM org_members WHERE user_id = auth.uid();
$$;

ALTER TABLE organizations ENABLE ROW LEVEL SECURITY;
ALTER TABLE org_members ENABLE ROW LEVEL SECURITY;
ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;

-- organizations: members can see (and, for owners/admins, update) the
-- orgs they belong to. Provisioning (SSO) and cross-org writes always
-- go through the service-role client, which bypasses RLS entirely —
-- these policies only cover the browser/session-scoped client.
DROP POLICY IF EXISTS "Members can view their organizations" ON organizations;
CREATE POLICY "Members can view their organizations" ON organizations
  FOR SELECT USING (id IN (SELECT public.user_org_ids()));

DROP POLICY IF EXISTS "Owners and admins can update their organizations" ON organizations;
CREATE POLICY "Owners and admins can update their organizations" ON organizations
  FOR UPDATE USING (
    id IN (
      SELECT org_id FROM org_members
      WHERE user_id = auth.uid() AND role IN ('owner', 'admin')
    )
  );

-- org_members: users can see membership rows for orgs they belong to.
DROP POLICY IF EXISTS "Members can view org membership" ON org_members;
CREATE POLICY "Members can view org membership" ON org_members
  FOR SELECT USING (org_id IN (SELECT public.user_org_ids()));

-- profiles: strictly self-scoped.
DROP POLICY IF EXISTS "Users can view own profile" ON profiles;
CREATE POLICY "Users can view own profile" ON profiles
  FOR SELECT USING (auth.uid() = id);

DROP POLICY IF EXISTS "Users can update own profile" ON profiles;
CREATE POLICY "Users can update own profile" ON profiles
  FOR UPDATE USING (auth.uid() = id);

DROP POLICY IF EXISTS "Users can insert own profile" ON profiles;
CREATE POLICY "Users can insert own profile" ON profiles
  FOR INSERT WITH CHECK (auth.uid() = id);
