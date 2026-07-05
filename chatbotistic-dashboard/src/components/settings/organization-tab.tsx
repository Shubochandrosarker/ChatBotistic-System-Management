"use client";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input, Label } from "@/components/ui/input";
import { createClient } from "@/lib/supabase/client";
import type { OrgRow } from "@/lib/org-context";
import { FEATURE_LABELS, PLANS, planForFeature, planOf, can, limitOf, type Feature, type Limit } from "@/lib/plans";
import { formatNumber } from "@/lib/utils";
import { CheckCircle2, Lock } from "lucide-react";
import { useState } from "react";

const FEATURES = Object.keys(FEATURE_LABELS) as Feature[];

const LIMIT_ROWS: { key: Limit; label: string; current?: number; note?: string }[] = [
  { key: "widgets", label: "Widgets" },
  { key: "agents", label: "Agents", note: "Tracked on Tochat — not mirrored locally yet." },
  { key: "domains", label: "Domains", note: "Tracked on Tochat — not mirrored locally yet." },
  { key: "seats", label: "Seats" },
  { key: "messages_per_month", label: "Messages / month", note: "See the Campaigns page for this period's usage." },
];

export function OrganizationTab({
  org,
  usage,
  canEdit,
}: {
  org: OrgRow;
  usage: { widgets: number; seats: number };
  canEdit: boolean;
}) {
  const [name, setName] = useState(org.name);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [saved, setSaved] = useState(false);

  const plan = planOf(org);
  const currentByLimit: Partial<Record<Limit, number>> = { widgets: usage.widgets, seats: usage.seats };

  async function saveName() {
    setSaving(true);
    setError(null);
    setSaved(false);
    const supabase = createClient();
    // organizations has an owner/admin-only UPDATE policy
    // (002_rls.sql) — a non-owner/admin session's write here is
    // silently ignored by RLS (0 rows affected) rather than erroring,
    // hence the explicit `canEdit` gate in the UI.
    const { error: updateError } = await supabase.from("organizations").update({ name }).eq("id", org.id);
    setSaving(false);
    if (updateError) setError(updateError.message);
    else setSaved(true);
  }

  return (
    <div className="flex flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Organization</CardTitle>
          <CardDescription>
            {canEdit ? "Only owners and admins can rename the organization." : "Only owners and admins can edit these settings."}
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-3 pt-4">
          <div className="max-w-sm">
            <Label htmlFor="org-name">Organization name</Label>
            <Input id="org-name" value={name} onChange={(e) => setName(e.target.value)} disabled={!canEdit} />
          </div>
          {error && <p className="text-sm text-destructive">{error}</p>}
          {canEdit && (
            <div className="flex items-center gap-2">
              <Button onClick={saveName} loading={saving}>
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

      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>Plan</CardTitle>
            <Badge variant="default">{plan.name}</Badge>
          </div>
          <CardDescription>{plan.tagline}</CardDescription>
        </CardHeader>
        <CardContent className="pt-4">
          <div className="overflow-x-auto">
            <table className="w-full min-w-[420px] text-left text-sm">
              <thead>
                <tr className="border-b border-border text-xs uppercase tracking-wide text-muted-foreground">
                  <th className="py-2 font-medium">Entitlement</th>
                  <th className="py-2 font-medium">Usage</th>
                  <th className="py-2 font-medium">Limit</th>
                </tr>
              </thead>
              <tbody>
                {LIMIT_ROWS.map((row) => {
                  const limit = limitOf(org, row.key);
                  const current = currentByLimit[row.key];
                  return (
                    <tr key={row.key} className="border-b border-border last:border-0">
                      <td className="py-2">
                        {row.label}
                        {row.note && <p className="text-xs text-muted-foreground">{row.note}</p>}
                      </td>
                      <td className="py-2">{current !== undefined ? formatNumber(current) : "—"}</td>
                      <td className="py-2">{limit < 0 ? "Unlimited" : formatNumber(limit)}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Features</CardTitle>
          <CardDescription>What&apos;s unlocked on your current plan.</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-2 pt-4">
          {FEATURES.map((feature) => {
            const enabled = can(org, feature);
            const unlockPlan = planForFeature(feature);
            return (
              <div key={feature} className="flex items-center justify-between rounded-xl border border-border px-3 py-2">
                <span className="text-sm">{FEATURE_LABELS[feature]}</span>
                {enabled ? (
                  <Badge variant="success">
                    <CheckCircle2 className="h-3 w-3" />
                    Enabled
                  </Badge>
                ) : (
                  <Badge variant="outline">
                    <Lock className="h-3 w-3" />
                    {PLANS[unlockPlan.id].name}+
                  </Badge>
                )}
              </div>
            );
          })}
        </CardContent>
      </Card>
    </div>
  );
}
