-- ============================================================
-- 005 — Email delivery log
--
-- `org_id` is nullable because some sends happen before an org exists
-- (e.g. a failed-signup notice) or are platform-level (not org
-- scoped). The service-role client (used by whatever mailer sends
-- these) bypasses RLS naturally, so it can always insert regardless
-- of org_id; the policy below only governs what a signed-in user's
-- session client can read.
--
-- Idempotent — safe to run multiple times.
-- ============================================================

CREATE TABLE IF NOT EXISTS email_log (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID REFERENCES organizations(id) ON DELETE SET NULL,
  to_email TEXT NOT NULL,
  template TEXT,
  status TEXT NOT NULL DEFAULT 'sent',
  provider_id TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_email_log_org ON email_log(org_id);

ALTER TABLE email_log ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Org members view email_log" ON email_log;
CREATE POLICY "Org members view email_log" ON email_log FOR SELECT
  USING (org_id IS NOT NULL AND org_id IN (SELECT public.user_org_ids()));
