-- ============================================================
-- 004 — Campaigns, message usage metering, WhatsApp connections
--
-- `message_usage` tracks the org's monthly send volume against
-- PLANS[plan].limits.messages_per_month (src/lib/plans.ts,
-- currentPeriodMonth()). `increment_message_usage` is the single
-- write path so callers never race a read-then-write on the counter.
--
-- `whatsapp_connections` stores the org's own WhatsApp connection
-- (Meta Cloud API or a "personal" number bridge); credentials are
-- encrypted at rest with src/lib/encryption.ts before being written
-- to `credentials_encrypted`.
--
-- Idempotent — safe to run multiple times.
-- ============================================================

CREATE TABLE IF NOT EXISTS campaigns (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  name TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'draft'
    CHECK (status IN ('draft', 'scheduled', 'sending', 'sent')),
  message_body TEXT,
  audience JSONB,
  scheduled_at TIMESTAMPTZ,
  sent_count INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_campaigns_org ON campaigns(org_id);

DROP TRIGGER IF EXISTS set_updated_at ON campaigns;
CREATE TRIGGER set_updated_at BEFORE UPDATE ON campaigns
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- ============================================================
-- MESSAGE_USAGE — one row per org per calendar month ("2026-07").
-- ============================================================
CREATE TABLE IF NOT EXISTS message_usage (
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  period_month TEXT NOT NULL,
  messages_sent INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (org_id, period_month)
);

-- SECURITY DEFINER so it can upsert/increment regardless of the
-- calling role's RLS grants — callers (proxy routes) have already
-- verified the org before invoking this.
CREATE OR REPLACE FUNCTION public.increment_message_usage(
  p_org_id UUID,
  p_period TEXT,
  p_count INTEGER DEFAULT 1
)
RETURNS INTEGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  new_total INTEGER;
BEGIN
  INSERT INTO public.message_usage (org_id, period_month, messages_sent)
  VALUES (p_org_id, p_period, GREATEST(p_count, 0))
  ON CONFLICT (org_id, period_month)
  DO UPDATE SET messages_sent = message_usage.messages_sent + GREATEST(EXCLUDED.messages_sent, 0)
  RETURNING messages_sent INTO new_total;

  RETURN new_total;
END;
$$;

ALTER FUNCTION public.increment_message_usage(UUID, TEXT, INTEGER) OWNER TO postgres;

-- ============================================================
-- WHATSAPP_CONNECTIONS — one connection per org.
-- ============================================================
CREATE TABLE IF NOT EXISTS whatsapp_connections (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL UNIQUE REFERENCES organizations(id) ON DELETE CASCADE,
  provider TEXT NOT NULL DEFAULT 'personal'
    CHECK (provider IN ('meta', 'personal')),
  phone_number TEXT,
  credentials_encrypted TEXT,
  status TEXT NOT NULL DEFAULT 'disconnected',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

DROP TRIGGER IF EXISTS set_updated_at ON whatsapp_connections;
CREATE TRIGGER set_updated_at BEFORE UPDATE ON whatsapp_connections
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- ============================================================
-- RLS
-- ============================================================
ALTER TABLE campaigns ENABLE ROW LEVEL SECURITY;
ALTER TABLE message_usage ENABLE ROW LEVEL SECURITY;
ALTER TABLE whatsapp_connections ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Org members manage campaigns" ON campaigns;
CREATE POLICY "Org members manage campaigns" ON campaigns FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));

DROP POLICY IF EXISTS "Org members manage message_usage" ON message_usage;
CREATE POLICY "Org members manage message_usage" ON message_usage FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));

DROP POLICY IF EXISTS "Org members manage whatsapp_connections" ON whatsapp_connections;
CREATE POLICY "Org members manage whatsapp_connections" ON whatsapp_connections FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));
