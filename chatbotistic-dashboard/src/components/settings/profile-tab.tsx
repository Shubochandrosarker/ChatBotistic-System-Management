"use client";

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input, Label } from "@/components/ui/input";
import { createClient } from "@/lib/supabase/client";
import { CheckCircle2, User } from "lucide-react";
import { useState } from "react";

export function ProfileTab({
  userId,
  userEmail,
  initialProfile,
}: {
  userId: string;
  userEmail: string;
  initialProfile: { full_name: string | null; avatar_url: string | null };
}) {
  const [fullName, setFullName] = useState(initialProfile.full_name ?? "");
  const [savingName, setSavingName] = useState(false);
  const [nameSaved, setNameSaved] = useState(false);

  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [savingPassword, setSavingPassword] = useState(false);
  const [passwordMessage, setPasswordMessage] = useState<string | null>(null);

  async function saveName() {
    setSavingName(true);
    setNameSaved(false);
    const supabase = createClient();
    const { error } = await supabase.from("profiles").update({ full_name: fullName }).eq("id", userId);
    setSavingName(false);
    if (!error) setNameSaved(true);
  }

  async function changePassword() {
    setPasswordMessage(null);
    if (newPassword.length < 8) {
      setPasswordMessage("Password must be at least 8 characters.");
      return;
    }
    if (newPassword !== confirmPassword) {
      setPasswordMessage("Passwords don't match.");
      return;
    }
    setSavingPassword(true);
    const supabase = createClient();
    const { error } = await supabase.auth.updateUser({ password: newPassword });
    setSavingPassword(false);
    if (error) {
      setPasswordMessage(error.message);
    } else {
      setPasswordMessage("Password updated.");
      setNewPassword("");
      setConfirmPassword("");
    }
  }

  return (
    <div className="flex flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Profile</CardTitle>
          <CardDescription>Your name and photo, as your teammates see them.</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-4 pt-4">
          <div className="flex items-center gap-4">
            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground">
              <User className="h-7 w-7" />
            </div>
            <div>
              <input type="file" disabled className="text-sm text-muted-foreground" aria-label="Upload avatar" />
              <p className="mt-1 text-xs text-muted-foreground">
                Avatar upload isn&apos;t wired up yet — no Supabase Storage bucket is configured for this
                project. This control is a stub.
              </p>
            </div>
          </div>

          <div className="max-w-sm">
            <Label htmlFor="full-name">Full name</Label>
            <Input id="full-name" value={fullName} onChange={(e) => setFullName(e.target.value)} />
          </div>

          <div className="max-w-sm">
            <Label>Email</Label>
            <Input value={userEmail} disabled />
          </div>

          <div className="flex items-center gap-2">
            <Button onClick={saveName} loading={savingName}>
              Save name
            </Button>
            {nameSaved && (
              <span className="inline-flex items-center gap-1 text-sm text-success">
                <CheckCircle2 className="h-4 w-4" />
                Saved
              </span>
            )}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Password</CardTitle>
          <CardDescription>Change the password used to sign in.</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-4 pt-4">
          <div className="max-w-sm">
            <Label htmlFor="new-password">New password</Label>
            <Input
              id="new-password"
              type="password"
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
              autoComplete="new-password"
            />
          </div>
          <div className="max-w-sm">
            <Label htmlFor="confirm-password">Confirm password</Label>
            <Input
              id="confirm-password"
              type="password"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              autoComplete="new-password"
            />
          </div>
          <div className="flex items-center gap-2">
            <Button onClick={changePassword} loading={savingPassword}>
              Update password
            </Button>
            {passwordMessage && <span className="text-sm text-muted-foreground">{passwordMessage}</span>}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
