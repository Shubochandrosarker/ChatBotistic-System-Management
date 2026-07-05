"use client";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input, Label } from "@/components/ui/input";
import { createClient } from "@/lib/supabase/client";
import { CheckCircle2, Lock } from "lucide-react";
import { useState } from "react";

interface WhiteLabelConfig {
  brandName?: string;
  primaryColor?: string;
  customDomain?: string;
  emailSenderName?: string;
}

export function WhiteLabelTab({
  orgId,
  initialWhiteLabel,
  allowed,
  canEdit,
}: {
  orgId: string;
  initialWhiteLabel: Record<string, unknown> | null;
  allowed: boolean;
  canEdit: boolean;
}) {
  const initial = (initialWhiteLabel ?? {}) as WhiteLabelConfig;
  const [brandName, setBrandName] = useState(initial.brandName ?? "");
  const [primaryColor, setPrimaryColor] = useState(initial.primaryColor ?? "#25D366");
  const [customDomain, setCustomDomain] = useState(initial.customDomain ?? "");
  const [emailSenderName, setEmailSenderName] = useState(initial.emailSenderName ?? "");
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState<string | null>(null);

  if (!allowed) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>White-label</CardTitle>
          <CardDescription>Rebrand the dashboard and widget for your clients.</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col items-center gap-3 py-10 text-center">
          <Badge variant="outline">
            <Lock className="h-3 w-3" />
            Agency plan
          </Badge>
          <p className="max-w-sm text-sm text-muted-foreground">
            White-label branding, custom domains, and sender names are available on the Agency plan. Upgrade
            to unlock this tab.
          </p>
        </CardContent>
      </Card>
    );
  }

  async function save() {
    setSaving(true);
    setSaved(false);
    setError(null);
    const supabase = createClient();
    const whiteLabel: WhiteLabelConfig = { brandName, primaryColor, customDomain, emailSenderName };
    // organizations has an owner/admin-only UPDATE policy (002_rls.sql).
    const { error: updateError } = await supabase
      .from("organizations")
      .update({ white_label: whiteLabel })
      .eq("id", orgId);
    setSaving(false);
    if (updateError) setError(updateError.message);
    else setSaved(true);
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>White-label</CardTitle>
        <CardDescription>
          {canEdit ? "Rebrand the dashboard and widget for your clients." : "Only owners and admins can edit these settings."}
        </CardDescription>
      </CardHeader>
      <CardContent className="flex flex-col gap-4 pt-4">
        <div className="max-w-sm">
          <Label htmlFor="brand-name">Brand name</Label>
          <Input id="brand-name" value={brandName} onChange={(e) => setBrandName(e.target.value)} disabled={!canEdit} />
        </div>
        <div className="max-w-sm">
          <Label htmlFor="primary-color">Primary color</Label>
          <div className="flex items-center gap-2">
            <input
              type="color"
              value={primaryColor}
              onChange={(e) => setPrimaryColor(e.target.value)}
              disabled={!canEdit}
              className="h-10 w-12 rounded-lg border border-input bg-background"
              aria-label="Primary color"
            />
            <Input value={primaryColor} onChange={(e) => setPrimaryColor(e.target.value)} disabled={!canEdit} />
          </div>
        </div>
        <div className="max-w-sm">
          <Label htmlFor="email-sender">Email sender name</Label>
          <Input
            id="email-sender"
            value={emailSenderName}
            onChange={(e) => setEmailSenderName(e.target.value)}
            disabled={!canEdit}
            placeholder="Acme Support"
          />
        </div>
        <div className="max-w-sm">
          <Label htmlFor="custom-domain">Custom domain</Label>
          <Input
            id="custom-domain"
            value={customDomain}
            onChange={(e) => setCustomDomain(e.target.value)}
            disabled={!canEdit}
            placeholder="chat.yourbrand.com"
          />
          <p className="mt-1 text-xs text-muted-foreground">
            Point a CNAME record for this subdomain at{" "}
            <code className="rounded bg-muted px-1 py-0.5">dashboard.chatbotistic.com</code>. DNS changes can
            take up to 24 hours to propagate; SSL is issued automatically once it resolves.
          </p>
        </div>

        {error && <p className="text-sm text-destructive">{error}</p>}

        {canEdit && (
          <div className="flex items-center gap-2">
            <Button onClick={save} loading={saving}>
              Save
            </Button>
            {saved && (
              <span className="inline-flex items-center gap-1 text-sm text-success">
                <CheckCircle2 className="h-4 w-4" />
                Saved
              </span>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
