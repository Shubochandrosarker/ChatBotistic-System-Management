-- ============================================================
-- 003 — Widgets cache, leads, landing pages
--
-- `widgets_cache` mirrors Tochat.be widgets so the dashboard can list/
-- read without hitting Tochat on every request (src/app/api/tochat/
-- widgets routes upsert into it on every successful write, and on
-- explicit `?sync=1` reads).
--
-- `leads` mirrors Tochat's /api/v2/stats rows (src/app/api/leads/sync
-- pulls and upserts them, deduped on tochat_stat_id).
--
-- `landing_pages` is dashboard-only config (no Tochat equivalent).
--
-- All three are org-scoped via the `user_org_ids()` helper from 002.
-- Idempotent — safe to run multiple times.
-- ============================================================

CREATE TABLE IF NOT EXISTS widgets_cache (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  tochat_widget_id TEXT NOT NULL,
  name TEXT,
  settings JSONB,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  synced_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (org_id, tochat_widget_id)
);

CREATE INDEX IF NOT EXISTS idx_widgets_cache_org ON widgets_cache(org_id);

CREATE TABLE IF NOT EXISTS leads (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  tochat_stat_id TEXT NOT NULL,
  phone TEXT,
  name TEXT,
  country TEXT,
  referer TEXT,
  widget_id TEXT,
  agent TEXT,
  fields JSONB,
  booking_data JSONB,
  status TEXT NOT NULL DEFAULT 'open'
    CHECK (status IN ('open', 'contacted', 'won', 'lost')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (org_id, tochat_stat_id)
);

CREATE INDEX IF NOT EXISTS idx_leads_org ON leads(org_id);
CREATE INDEX IF NOT EXISTS idx_leads_widget ON leads(widget_id);
CREATE INDEX IF NOT EXISTS idx_leads_status ON leads(status);

CREATE TABLE IF NOT EXISTS landing_pages (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  widget_id TEXT,
  settings JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_landing_pages_org ON landing_pages(org_id);

DROP TRIGGER IF EXISTS set_updated_at ON landing_pages;
CREATE TRIGGER set_updated_at BEFORE UPDATE ON landing_pages
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- ============================================================
-- RLS — org_id IN (SELECT user_org_ids()) on every table, no
-- set_org_id trigger needed: the tochat proxy routes always set
-- org_id explicitly from the caller's resolved org.
-- ============================================================
ALTER TABLE widgets_cache ENABLE ROW LEVEL SECURITY;
ALTER TABLE leads ENABLE ROW LEVEL SECURITY;
ALTER TABLE landing_pages ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Org members manage widgets_cache" ON widgets_cache;
CREATE POLICY "Org members manage widgets_cache" ON widgets_cache FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));

DROP POLICY IF EXISTS "Org members manage leads" ON leads;
CREATE POLICY "Org members manage leads" ON leads FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));

DROP POLICY IF EXISTS "Org members manage landing_pages" ON landing_pages;
CREATE POLICY "Org members manage landing_pages" ON landing_pages FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));
