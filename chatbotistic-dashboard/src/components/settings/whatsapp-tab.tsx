"use client";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input, Label } from "@/components/ui/input";
import { AlertCircle, CheckCircle2, Copy, MessageCircle, Unplug } from "lucide-react";
import { useCallback, useEffect, useState } from "react";

export interface WhatsAppConnection {
  provider: "meta" | "personal" | "twilio";
  phone_number: string | null;
  status: string;
}

/** GET /api/settings/whatsapp response shape. */
interface ConnectionState {
  provider: "meta" | "personal" | "twilio";
  phoneNumber: string | null;
  status: string;
  lastVerifiedAt: string | null;
  webhookVerifyToken: string | null;
  metaWebhookUrl: string;
  twilioWebhookUrl: string;
}

async function postWhatsapp(body: Record<string, unknown>) {
  const res = await fetch("/api/settings/whatsapp", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || "Request failed.");
  return data;
}

function CopyField({ label, value }: { label: string; value: string }) {
  const [copied, setCopied] = useState(false);
  async function copy() {
    try {
      await navigator.clipboard?.writeText(value);
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch {
      // Clipboard API unavailable (e.g. insecure context) — no-op.
    }
  }
  return (
    <div>
      <Label>{label}</Label>
      <div className="flex items-center gap-2">
        <Input readOnly value={value} className="font-mono text-xs" />
        <Button type="button" variant="outline" size="icon" onClick={copy} aria-label={`Copy ${label}`}>
          {copied ? <CheckCircle2 className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
        </Button>
      </div>
    </div>
  );
}

export function WhatsAppTab({ initialConnection }: { initialConnection: WhatsAppConnection }) {
  const [mode, setMode] = useState<"personal" | "meta" | "twilio">(initialConnection.provider);
  // The provider actually saved server-side (independent of which tab
  // is currently open for editing) — used to gate the post-connect
  // webhook info so it doesn't show while previewing a different
  // provider's form.
  const [activeProvider, setActiveProvider] = useState<"personal" | "meta" | "twilio">(
    initialConnection.provider
  );
  const [status, setStatus] = useState(initialConnection.status);
  const [phoneNumber, setPhoneNumber] = useState(initialConnection.phone_number ?? "");

  // Meta fields
  const [phoneNumberId, setPhoneNumberId] = useState("");
  const [wabaId, setWabaId] = useState("");
  const [accessToken, setAccessToken] = useState("");
  const [appSecret, setAppSecret] = useState("");

  // Twilio fields
  const [accountSid, setAccountSid] = useState("");
  const [authToken, setAuthToken] = useState("");
  const [whatsappNumber, setWhatsappNumber] = useState("");

  // Post-connect info surfaced by the GET handler — never the
  // decrypted secret, just what the user needs to paste into Meta's /
  // Twilio's own dashboards.
  const [webhookVerifyToken, setWebhookVerifyToken] = useState<string | null>(null);
  const [metaWebhookUrl, setMetaWebhookUrl] = useState("");
  const [twilioWebhookUrl, setTwilioWebhookUrl] = useState("");

  const [saving, setSaving] = useState(false);
  const [testing, setTesting] = useState(false);
  const [message, setMessage] = useState<{ kind: "error" | "info"; text: string } | null>(null);

  const connected = status === "connected";

  const loadState = useCallback(async () => {
    try {
      const res = await fetch("/api/settings/whatsapp");
      const data = (await res.json()) as ConnectionState;
      if (!res.ok) return;
      setMode(data.provider);
      setActiveProvider(data.provider);
      setStatus(data.status);
      setPhoneNumber(data.phoneNumber ?? "");
      setWebhookVerifyToken(data.webhookVerifyToken);
      setMetaWebhookUrl(data.metaWebhookUrl);
      setTwilioWebhookUrl(data.twilioWebhookUrl);
    } catch {
      // Keep the server-rendered initialConnection as a fallback.
    }
  }, []);

  useEffect(() => {
    loadState();
  }, [loadState]);

  async function save() {
    setSaving(true);
    setMessage(null);
    try {
      let data: { ok: boolean; webhookVerifyToken?: string };
      if (mode === "personal") {
        data = await postWhatsapp({ provider: "personal", phoneNumber });
      } else if (mode === "meta") {
        data = await postWhatsapp({
          provider: "meta",
          phoneNumber,
          phoneNumberId,
          wabaId,
          accessToken,
          appSecret,
        });
      } else {
        data = await postWhatsapp({
          provider: "twilio",
          phoneNumber,
          accountSid,
          authToken,
          whatsappNumber,
        });
      }
      setStatus("connected");
      setMessage({ kind: "info", text: "Connection saved." });
      setAccessToken("");
      setAppSecret("");
      setAuthToken("");
      if (data.webhookVerifyToken) setWebhookVerifyToken(data.webhookVerifyToken);
      // Refresh from the server so webhook URLs / token reflect what's
      // actually stored (also covers the "reused existing secret"
      // path when a masked field was left blank).
      await loadState();
    } catch (err) {
      setMessage({ kind: "error", text: err instanceof Error ? err.message : "Failed to save." });
    } finally {
      setSaving(false);
    }
  }

  async function test() {
    setTesting(true);
    setMessage(null);
    try {
      const data = await postWhatsapp({ action: "test" });
      setMessage({ kind: data.ok ? "info" : "error", text: data.message });
    } catch (err) {
      setMessage({ kind: "error", text: err instanceof Error ? err.message : "Test failed." });
    } finally {
      setTesting(false);
    }
  }

  async function disconnect() {
    setSaving(true);
    setMessage(null);
    try {
      await postWhatsapp({ action: "disconnect" });
      setStatus("disconnected");
      setMode("personal");
      setActiveProvider("personal");
      setPhoneNumber("");
      setPhoneNumberId("");
      setWabaId("");
      setAccessToken("");
      setAppSecret("");
      setAccountSid("");
      setAuthToken("");
      setWhatsappNumber("");
      setWebhookVerifyToken(null);
      setMessage({ kind: "info", text: "Disconnected." });
    } catch (err) {
      setMessage({ kind: "error", text: err instanceof Error ? err.message : "Failed to disconnect." });
    } finally {
      setSaving(false);
    }
  }

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle>WhatsApp connection</CardTitle>
          <Badge variant={connected ? "success" : "outline"}>
            <MessageCircle className="h-3 w-3" />
            {connected ? "Connected" : "Disconnected"}
          </Badge>
        </div>
        <CardDescription>
          Connect your own WhatsApp Cloud API account (Meta or Twilio). Messages you send through Campaigns and
          the Inbox count against your plan&apos;s monthly message quota — see the Organization tab for current
          usage.{" "}
          <a href="/docs/connect-whatsapp" className="underline underline-offset-2 hover:text-foreground">
            Read the setup guide
          </a>
          .
        </CardDescription>
      </CardHeader>
      <CardContent className="flex flex-col gap-4 pt-4">
        <div className="flex gap-2">
          <button
            type="button"
            onClick={() => setMode("personal")}
            className={`flex-1 rounded-xl border px-4 py-3 text-left text-sm transition-colors ${
              mode === "personal" ? "border-primary bg-accent" : "border-border hover:bg-muted"
            }`}
          >
            <p className="font-medium text-foreground">Personal number</p>
            <p className="text-xs text-muted-foreground">Leads only — you message manually from your own WhatsApp app.</p>
          </button>
          <button
            type="button"
            onClick={() => setMode("meta")}
            className={`flex-1 rounded-xl border px-4 py-3 text-left text-sm transition-colors ${
              mode === "meta" ? "border-primary bg-accent" : "border-border hover:bg-muted"
            }`}
          >
            <p className="font-medium text-foreground">Meta Cloud API</p>
            <p className="text-xs text-muted-foreground">Connect a WhatsApp Business number directly via Meta.</p>
          </button>
          <button
            type="button"
            onClick={() => setMode("twilio")}
            className={`flex-1 rounded-xl border px-4 py-3 text-left text-sm transition-colors ${
              mode === "twilio" ? "border-primary bg-accent" : "border-border hover:bg-muted"
            }`}
          >
            <p className="font-medium text-foreground">Twilio</p>
            <p className="text-xs text-muted-foreground">Connect a WhatsApp-enabled number via Twilio.</p>
          </button>
        </div>

        <div className="max-w-sm">
          <Label htmlFor="wa-phone">Phone number</Label>
          <Input
            id="wa-phone"
            value={phoneNumber}
            onChange={(e) => setPhoneNumber(e.target.value)}
            placeholder="+1 555 123 4567"
          />
        </div>

        {mode === "meta" && (
          <>
            <div className="max-w-sm">
              <Label htmlFor="wa-phone-id">Phone number ID</Label>
              <Input id="wa-phone-id" value={phoneNumberId} onChange={(e) => setPhoneNumberId(e.target.value)} />
            </div>
            <div className="max-w-sm">
              <Label htmlFor="wa-waba-id">WhatsApp Business Account ID</Label>
              <Input id="wa-waba-id" value={wabaId} onChange={(e) => setWabaId(e.target.value)} />
            </div>
            <div className="max-w-sm">
              <Label htmlFor="wa-token">Access token</Label>
              <Input
                id="wa-token"
                type="password"
                value={accessToken}
                onChange={(e) => setAccessToken(e.target.value)}
                placeholder={connected ? "•••••••• (unchanged if left blank)" : ""}
              />
            </div>
            <div className="max-w-sm">
              <Label htmlFor="wa-app-secret">App secret</Label>
              <Input
                id="wa-app-secret"
                type="password"
                value={appSecret}
                onChange={(e) => setAppSecret(e.target.value)}
                placeholder={connected ? "•••••••• (unchanged if left blank)" : ""}
              />
              <p className="mt-1 text-xs text-muted-foreground">
                Used to verify inbound webhook signatures from Meta. Encrypted at rest (AES-256-GCM) — never sent
                back to the browser after saving.
              </p>
            </div>

            {mode === activeProvider && connected && webhookVerifyToken && (
              <div className="flex flex-col gap-3 rounded-xl border border-border bg-muted/40 p-3">
                <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                  Configure this in your Meta App&apos;s webhook subscription settings
                </p>
                <CopyField label="Callback URL" value={metaWebhookUrl} />
                <CopyField label="Verify token" value={webhookVerifyToken} />
              </div>
            )}
          </>
        )}

        {mode === "twilio" && (
          <>
            <div className="max-w-sm">
              <Label htmlFor="wa-account-sid">Account SID</Label>
              <Input id="wa-account-sid" value={accountSid} onChange={(e) => setAccountSid(e.target.value)} />
            </div>
            <div className="max-w-sm">
              <Label htmlFor="wa-auth-token">Auth token</Label>
              <Input
                id="wa-auth-token"
                type="password"
                value={authToken}
                onChange={(e) => setAuthToken(e.target.value)}
                placeholder={connected ? "•••••••• (unchanged if left blank)" : ""}
              />
            </div>
            <div className="max-w-sm">
              <Label htmlFor="wa-whatsapp-number">WhatsApp-enabled number</Label>
              <Input
                id="wa-whatsapp-number"
                value={whatsappNumber}
                onChange={(e) => setWhatsappNumber(e.target.value)}
                placeholder="+14155238886"
              />
              <p className="mt-1 text-xs text-muted-foreground">
                Encrypted at rest (AES-256-GCM) before it&apos;s stored — never sent to the browser again.
              </p>
            </div>

            {mode === activeProvider && connected && (
              <div className="flex flex-col gap-3 rounded-xl border border-border bg-muted/40 p-3">
                <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                  Paste this into Twilio Console → your WhatsApp sender → &quot;WHEN A MESSAGE COMES IN&quot;
                </p>
                <CopyField label="Webhook URL" value={twilioWebhookUrl} />
              </div>
            )}
          </>
        )}

        {message && (
          <div
            role="alert"
            className={`flex items-start gap-2 rounded-xl px-3.5 py-2.5 text-sm ${
              message.kind === "error" ? "bg-destructive/10 text-destructive" : "bg-accent text-accent-foreground"
            }`}
          >
            {message.kind === "error" ? (
              <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
            ) : (
              <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" />
            )}
            <span>{message.text}</span>
          </div>
        )}

        <div className="flex flex-wrap items-center gap-2">
          <Button onClick={save} loading={saving}>
            Save connection
          </Button>
          <Button variant="outline" onClick={test} loading={testing}>
            Test connection
          </Button>
          {connected && (
            <Button variant="ghost" onClick={disconnect} loading={saving}>
              <Unplug className="h-4 w-4" />
              Disconnect
            </Button>
          )}
        </div>
        <p className="text-xs text-muted-foreground">
          &quot;Test connection&quot; makes a live, read-only call to the saved provider (Meta Graph API or
          Twilio&apos;s Accounts API) to confirm the credentials work. A failed test won&apos;t disconnect an
          otherwise-working connection — it just reports the result here.
        </p>
      </CardContent>
    </Card>
  );
}
