"use client";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input, Label } from "@/components/ui/input";
import { AlertCircle, CheckCircle2, MessageCircle, Unplug } from "lucide-react";
import { useState } from "react";

export interface WhatsAppConnection {
  provider: "meta" | "personal";
  phone_number: string | null;
  status: string;
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

export function WhatsAppTab({ initialConnection }: { initialConnection: WhatsAppConnection }) {
  const [mode, setMode] = useState<"personal" | "meta">(initialConnection.provider);
  const [status, setStatus] = useState(initialConnection.status);
  const [phoneNumber, setPhoneNumber] = useState(initialConnection.phone_number ?? "");
  const [phoneNumberId, setPhoneNumberId] = useState("");
  const [wabaId, setWabaId] = useState("");
  const [accessToken, setAccessToken] = useState("");
  const [saving, setSaving] = useState(false);
  const [testing, setTesting] = useState(false);
  const [message, setMessage] = useState<{ kind: "error" | "info"; text: string } | null>(null);

  const connected = status === "connected";

  async function save() {
    setSaving(true);
    setMessage(null);
    try {
      if (mode === "personal") {
        await postWhatsapp({ provider: "personal", phoneNumber });
      } else {
        await postWhatsapp({ provider: "meta", phoneNumber, phoneNumberId, wabaId, accessToken });
      }
      setStatus("connected");
      setMessage({ kind: "info", text: "Connection saved." });
      setAccessToken("");
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
      setPhoneNumber("");
      setPhoneNumberId("");
      setWabaId("");
      setAccessToken("");
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
        <CardDescription>Choose how campaigns and chats reach your leads on WhatsApp.</CardDescription>
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
            <p className="text-xs text-muted-foreground">Connect a WhatsApp Business number to send campaigns from here.</p>
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
              <Label htmlFor="wa-waba-id">WABA ID</Label>
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
              <p className="mt-1 text-xs text-muted-foreground">
                Encrypted at rest (AES-256-GCM) before it&apos;s stored — never sent to the browser again.
              </p>
            </div>
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
          &quot;Test connection&quot; only checks that saved credentials look well-formed — it doesn&apos;t call the
          Meta Graph API yet, since campaign sending itself isn&apos;t implemented in this build.
        </p>
      </CardContent>
    </Card>
  );
}
