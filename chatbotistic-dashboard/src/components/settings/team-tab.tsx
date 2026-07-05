"use client";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input, Label } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { createClient } from "@/lib/supabase/client";
import { formatDate } from "@/lib/utils";
import { AlertCircle, CheckCircle2, Copy, Trash2 } from "lucide-react";
import { useCallback, useEffect, useState } from "react";

interface Member {
  id: string;
  userId: string;
  role: "owner" | "admin" | "agent";
  isPrimary: boolean;
  createdAt: string;
  name: string | null;
  email: string | null;
}

interface Invite {
  id: string;
  email: string;
  role: "admin" | "agent";
  token: string;
  accepted: boolean;
  created_at: string;
}

export function TeamTab({ orgId, isOwnerOrAdmin }: { orgId: string; isOwnerOrAdmin: boolean }) {
  const [members, setMembers] = useState<Member[]>([]);
  const [invites, setInvites] = useState<Invite[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [inviteEmail, setInviteEmail] = useState("");
  const [inviteRole, setInviteRole] = useState<"admin" | "agent">("agent");
  const [inviting, setInviting] = useState(false);

  const [acceptToken, setAcceptToken] = useState("");
  const [accepting, setAccepting] = useState(false);
  const [acceptMessage, setAcceptMessage] = useState<string | null>(null);

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch("/api/settings/team");
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Failed to load team.");
      setMembers(data.members ?? []);
      setInvites(data.invites ?? []);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to load team.");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  async function sendInvite() {
    if (!inviteEmail.trim()) return;
    setInviting(true);
    setError(null);
    const supabase = createClient();
    const token = crypto.randomUUID().replace(/-/g, "");
    // org_invites has an owner/admin-only `FOR ALL` policy
    // (006_org_invites.sql) — a direct browser insert is fine here,
    // unlike org_members, which has no user-writable policy at all.
    const { error: insertError } = await supabase.from("org_invites").insert({
      org_id: orgId,
      email: inviteEmail.trim().toLowerCase(),
      role: inviteRole,
      token,
    });
    setInviting(false);
    if (insertError) {
      setError(insertError.message);
      return;
    }
    setInviteEmail("");
    await load();
  }

  async function revokeInvite(id: string) {
    const supabase = createClient();
    await supabase.from("org_invites").delete().eq("id", id);
    await load();
  }

  async function removeMember(memberId: string) {
    const res = await fetch(`/api/settings/team?memberId=${memberId}`, { method: "DELETE" });
    if (res.ok) await load();
  }

  async function changeRole(memberId: string, role: "admin" | "agent") {
    const res = await fetch("/api/settings/team", {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ memberId, role }),
    });
    if (res.ok) await load();
  }

  function extractToken(raw: string): string {
    const trimmed = raw.trim();
    try {
      const url = new URL(trimmed);
      return url.searchParams.get("invite") || trimmed;
    } catch {
      return trimmed;
    }
  }

  async function acceptInvite() {
    setAccepting(true);
    setAcceptMessage(null);
    try {
      const res = await fetch("/api/settings/team/accept", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token: extractToken(acceptToken) }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Failed to accept invite.");
      setAcceptMessage("Invite accepted — reload the page to switch into that organization.");
      setAcceptToken("");
    } catch (err) {
      setAcceptMessage(err instanceof Error ? err.message : "Failed to accept invite.");
    } finally {
      setAccepting(false);
    }
  }

  function inviteLink(token: string) {
    if (typeof window === "undefined") return token;
    return `${window.location.origin}/settings?tab=team&invite=${token}`;
  }

  return (
    <div className="flex flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Members</CardTitle>
          <CardDescription>Everyone with access to this organization.</CardDescription>
        </CardHeader>
        <CardContent className="pt-4">
          {error && <p className="mb-3 text-sm text-destructive">{error}</p>}
          {loading ? (
            <p className="text-sm text-muted-foreground">Loading…</p>
          ) : members.length === 0 ? (
            <p className="text-sm text-muted-foreground">No members found.</p>
          ) : (
            <div className="flex flex-col gap-2">
              {members.map((m) => (
                <div key={m.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-border px-3 py-2">
                  <div>
                    <p className="text-sm font-medium text-foreground">{m.name || m.email || "Member"}</p>
                    <p className="text-xs text-muted-foreground">{m.email}</p>
                  </div>
                  <div className="flex items-center gap-2">
                    {m.role === "owner" ? (
                      <Badge variant="default">Owner</Badge>
                    ) : isOwnerOrAdmin ? (
                      <Select
                        value={m.role}
                        onChange={(e) => changeRole(m.id, e.target.value as "admin" | "agent")}
                        className="h-8 w-28 text-xs"
                      >
                        <option value="admin">Admin</option>
                        <option value="agent">Agent</option>
                      </Select>
                    ) : (
                      <Badge variant="outline">{m.role}</Badge>
                    )}
                    {isOwnerOrAdmin && m.role !== "owner" && (
                      <button
                        type="button"
                        onClick={() => removeMember(m.id)}
                        aria-label="Remove member"
                        className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {isOwnerOrAdmin && (
        <Card>
          <CardHeader>
            <CardTitle>Invite a teammate</CardTitle>
            <CardDescription>
              Invite emails aren&apos;t wired to a mail sender yet — after creating an invite, copy its link and
              share it yourself (Slack, email, whatever). The invitee needs a Chatbotistic account first (they
              can sign up normally), then pastes the token below to join.
            </CardDescription>
          </CardHeader>
          <CardContent className="flex flex-col gap-3 pt-4">
            <div className="flex flex-wrap items-end gap-2">
              <div className="min-w-[220px] flex-1">
                <Label htmlFor="invite-email">Email</Label>
                <Input
                  id="invite-email"
                  type="email"
                  value={inviteEmail}
                  onChange={(e) => setInviteEmail(e.target.value)}
                  placeholder="teammate@company.com"
                />
              </div>
              <div className="w-32">
                <Label htmlFor="invite-role">Role</Label>
                <Select id="invite-role" value={inviteRole} onChange={(e) => setInviteRole(e.target.value as "admin" | "agent")}>
                  <option value="agent">Agent</option>
                  <option value="admin">Admin</option>
                </Select>
              </div>
              <Button onClick={sendInvite} loading={inviting}>
                Create invite
              </Button>
            </div>

            {invites.filter((i) => !i.accepted).length > 0 && (
              <div className="flex flex-col gap-2">
                <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Pending invites</p>
                {invites
                  .filter((i) => !i.accepted)
                  .map((invite) => (
                    <div key={invite.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-border px-3 py-2 text-sm">
                      <div>
                        <p className="font-medium text-foreground">
                          {invite.email} <span className="text-xs font-normal text-muted-foreground">({invite.role})</span>
                        </p>
                        <p className="text-xs text-muted-foreground">Created {formatDate(invite.created_at)}</p>
                      </div>
                      <div className="flex items-center gap-2">
                        <button
                          type="button"
                          onClick={() => navigator.clipboard?.writeText(inviteLink(invite.token))}
                          className="inline-flex items-center gap-1 rounded-lg border border-border px-2 py-1 text-xs text-muted-foreground hover:bg-muted hover:text-foreground"
                        >
                          <Copy className="h-3.5 w-3.5" />
                          Copy link
                        </button>
                        <button
                          type="button"
                          onClick={() => revokeInvite(invite.id)}
                          className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                          aria-label="Revoke invite"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                  ))}
              </div>
            )}
          </CardContent>
        </Card>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Accept an invite</CardTitle>
          <CardDescription>Have an invite token or link from a teammate? Redeem it here.</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-3 pt-4">
          <div className="flex flex-wrap items-end gap-2">
            <div className="min-w-[220px] flex-1">
              <Label htmlFor="accept-token">Invite token</Label>
              <Input
                id="accept-token"
                value={acceptToken}
                onChange={(e) => setAcceptToken(e.target.value)}
                placeholder="Paste token or full invite link"
              />
            </div>
            <Button onClick={acceptInvite} loading={accepting} disabled={!acceptToken.trim()}>
              Accept
            </Button>
          </div>
          {acceptMessage && (
            <div className="flex items-start gap-2 text-sm text-muted-foreground">
              <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" />
              <span>{acceptMessage}</span>
            </div>
          )}
        </CardContent>
      </Card>

      {!isOwnerOrAdmin && (
        <div className="flex items-start gap-2 text-sm text-muted-foreground">
          <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
          <span>Only owners and admins can invite or remove teammates.</span>
        </div>
      )}
    </div>
  );
}
