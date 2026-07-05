-- ============================================================
-- 007 — WhatsApp provider widening (Twilio) + team Inbox schema
--
-- Widens `whatsapp_connections.provider` from ('meta','personal') to
-- also allow 'twilio' — orgs BYO either Meta's direct Cloud API or
-- Twilio's WhatsApp Business API. `credentials_encrypted` keeps its
-- single-blob shape (src/lib/encryption.ts AES-256-GCM); the JSON
-- inside just differs by provider — see src/lib/whatsapp/types.ts's
-- `StoredWhatsAppCredentials` for the exact per-provider shape.
-- `personal` stays credential-less (leads-only, no send/receive).
--
-- `webhook_verify_token` is used during Meta's webhook subscription
-- handshake (the `hub.verify_token` challenge Meta sends when an org
-- registers their webhook URL). `last_verified_at` records the last
-- successful credential check so Settings UI can show staleness.
--
-- New tables `conversations` / `messages` back the shared team Inbox
-- (the sidebar already links to /inbox; the page itself is built by
-- another agent on top of this schema). Both are org-scoped, not
-- per-agent: any team member can see and reply to any conversation in
-- their org, matching campaigns/whatsapp_connections' existing
-- "FOR ALL, org-scoped" RLS shape from 004.
--
-- Idempotent — safe to run multiple times. Follows 001-006 conventions
-- (IF NOT EXISTS for tables/columns/indexes, DROP ... IF EXISTS before
-- CREATE for policies/constraints — Postgres has no CREATE POLICY /
-- ADD CONSTRAINT IF NOT EXISTS).
-- ============================================================

-- ============================================================
-- WHATSAPP_CONNECTIONS — widen provider + webhook bookkeeping columns
-- ============================================================
ALTER TABLE whatsapp_connections DROP CONSTRAINT IF EXISTS whatsapp_connections_provider_check;
ALTER TABLE whatsapp_connections ADD CONSTRAINT whatsapp_connections_provider_check
  CHECK (provider IN ('personal', 'meta', 'twilio'));

ALTER TABLE whatsapp_connections ADD COLUMN IF NOT EXISTS webhook_verify_token TEXT;
ALTER TABLE whatsapp_connections ADD COLUMN IF NOT EXISTS last_verified_at TIMESTAMPTZ;

-- ============================================================
-- CONVERSATIONS — one row per contact phone number per org.
-- ============================================================
CREATE TABLE IF NOT EXISTS conversations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  contact_phone TEXT NOT NULL,
  contact_name TEXT,
  status TEXT NOT NULL DEFAULT 'open'
    CHECK (status IN ('open', 'pending', 'closed')),
  assigned_agent_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
  last_message_at TIMESTAMPTZ,
  unread_count INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (org_id, contact_phone)
);

CREATE INDEX IF NOT EXISTS idx_conversations_org ON conversations(org_id);
-- Inbox list view: an org's conversations ordered by most-recent activity.
CREATE INDEX IF NOT EXISTS idx_conversations_org_last_message
  ON conversations(org_id, last_message_at DESC);

-- ============================================================
-- MESSAGES — inbound/outbound message log, one row per WhatsApp message.
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  org_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
  direction TEXT NOT NULL CHECK (direction IN ('inbound', 'outbound')),
  body TEXT,
  provider_message_id TEXT,
  status TEXT NOT NULL DEFAULT 'sent'
    CHECK (status IN ('sent', 'delivered', 'read', 'failed')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_messages_org ON messages(org_id);
-- Conversation thread view: a conversation's messages in send order.
CREATE INDEX IF NOT EXISTS idx_messages_conversation_created
  ON messages(conversation_id, created_at);

-- ============================================================
-- RLS — org-scoped, team-wide (not restricted to assigned_agent_id).
-- ============================================================
ALTER TABLE conversations ENABLE ROW LEVEL SECURITY;
ALTER TABLE messages ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Org members manage conversations" ON conversations;
CREATE POLICY "Org members manage conversations" ON conversations FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));

DROP POLICY IF EXISTS "Org members manage messages" ON messages;
CREATE POLICY "Org members manage messages" ON messages FOR ALL
  USING (org_id IN (SELECT public.user_org_ids()))
  WITH CHECK (org_id IN (SELECT public.user_org_ids()));
