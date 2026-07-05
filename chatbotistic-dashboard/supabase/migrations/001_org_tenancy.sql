-- ============================================================
-- 001 — Organization tenancy: profiles, organizations, org_members
--
-- Bootstraps multi-tenancy for the Chatbotistic dashboard:
--   - `profiles`      one row per Supabase auth user.
--   - `organizations` the billing/tenancy unit. Plan + entitlements
--                     are asserted by the Memberistic + Licenseistic
--                     SSO bridge on chatbotistic.com (see
--                     src/lib/sso/provision.ts) and mirrored here.
--   - `org_members`   join table — a user can belong to more than one
--                     org (e.g. agency sub-accounts later).
--
-- Idempotent — safe to run multiple times. Follows the WPistic sibling
-- app's 009_org_tenancy.sql conventions: IF NOT EXISTS for tables/
-- indexes, DROP ... IF EXISTS before CREATE for policies/triggers
-- (Postgres has no CREATE POLICY/TRIGGER IF NOT EXISTS).
-- ============================================================

-- ============================================================
-- PROFILES — id doubles as the auth.users FK (no separate user_id).
-- ============================================================
CREATE TABLE IF NOT EXISTS profiles (
  id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  full_name TEXT,
  avatar_url TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ============================================================
-- ORGANIZATIONS
--
-- `plan` mirrors PlanId from src/lib/plans.ts (PLAN_ORDER).
-- `entitlements` mirrors OrgLike["entitlements"] — per-org overrides
-- on top of the plan's defaults; null until Memberistic asserts one.
-- `tochat_user_client` is the `org-{id}` tenancy tag handed to
-- Tochat.be, persisted by the proxy routes on first use (see
-- src/lib/tochat/client.ts's userClientForOrg()).
-- ============================================================
CREATE TABLE IF NOT EXISTS organizations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  plan TEXT NOT NULL DEFAULT 'free'
    CHECK (plan IN ('free', 'starter', 'growth', 'agency')),
  license_key TEXT,
  license_status TEXT NOT NULL DEFAULT 'inactive'
    CHECK (license_status IN ('active', 'inactive', 'expired', 'suspended')),
  entitlements JSONB,
  entitlements_synced_at TIMESTAMPTZ,
  tochat_user_client TEXT UNIQUE,
  white_label JSONB,
  -- Stable WordPress user/site id asserted by the SSO bridge — repeat
  -- logins from the same WordPress identity land on the same org.
  sso_subject TEXT UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ============================================================
-- ORG_MEMBERS
-- ============================================================
CREATE TABLE IF NOT EXISTS org_members (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  role TEXT NOT NULL DEFAULT 'agent'
    CHECK (role IN ('owner', 'admin', 'agent')),
  is_primary BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (org_id, user_id)
);

CREATE INDEX IF NOT EXISTS idx_org_members_user ON org_members(user_id);
CREATE INDEX IF NOT EXISTS idx_org_members_org ON org_members(org_id);

-- ============================================================
-- SHARED updated_at TRIGGER FUNCTION
-- (Later migrations attach this to tables that carry updated_at.)
-- ============================================================
CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================================
-- AUTO-PROVISION on signup.
--
-- Every new auth user gets a profile row and a personal organization
-- with an owner membership. The SSO login route
-- (src/app/api/sso/login/route.ts) calls
-- admin.auth.admin.createUser() *before* provisionFromClaims() runs,
-- so for a brand-new SSO user this trigger fires first, synchronously,
-- as part of the auth.users INSERT. The `NOT EXISTS` guard below is
-- defensive idempotency: if org_members already has a row for this
-- user by the time the trigger runs (a retried signup, a re-fired
-- trigger, or any provisioning race), we skip creating a second
-- personal org rather than leaving the user in two organizations.
-- Wrapped in an exception handler so signup/provisioning never fails
-- because of this trigger, matching the WPistic pattern.
-- ============================================================
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  new_org_id UUID;
BEGIN
  INSERT INTO public.profiles (id, full_name, avatar_url)
  VALUES (
    NEW.id,
    NULLIF(NEW.raw_user_meta_data->>'full_name', ''),
    NULLIF(NEW.raw_user_meta_data->>'avatar_url', '')
  )
  ON CONFLICT (id) DO NOTHING;

  IF NOT EXISTS (
    SELECT 1 FROM public.org_members WHERE user_id = NEW.id
  ) THEN
    INSERT INTO public.organizations (name)
    VALUES (
      COALESCE(NULLIF(NEW.raw_user_meta_data->>'full_name', ''), NEW.email, 'My Organization')
    )
    RETURNING id INTO new_org_id;

    INSERT INTO public.org_members (org_id, user_id, role, is_primary)
    VALUES (new_org_id, NEW.id, 'owner', TRUE)
    ON CONFLICT (org_id, user_id) DO NOTHING;
  END IF;

  RETURN NEW;
EXCEPTION WHEN OTHERS THEN
  RAISE WARNING 'handle_new_user failed for %: %', NEW.id, SQLERRM;
  RETURN NEW;
END;
$$;

ALTER FUNCTION public.handle_new_user() OWNER TO postgres;

DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();
