"use client";

import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import type { OrgRow } from "@/lib/org-context";
import { can } from "@/lib/plans";
import { Lock } from "lucide-react";
import { useState } from "react";
import { OrganizationTab } from "./organization-tab";
import { ProfileTab } from "./profile-tab";
import { TeamTab } from "./team-tab";
import { WhatsAppTab, type WhatsAppConnection } from "./whatsapp-tab";
import { WhiteLabelTab } from "./white-label-tab";

export interface SettingsTabsProps {
  initialTab?: string;
  userId: string;
  userEmail: string;
  org: OrgRow;
  role: "owner" | "admin" | "agent";
  profile: { full_name: string | null; avatar_url: string | null };
  connection: WhatsAppConnection;
  usage: { widgets: number; seats: number };
  whiteLabel: Record<string, unknown> | null;
}

const TAB_IDS = ["profile", "organization", "whatsapp", "white-label", "team"] as const;
type TabId = (typeof TAB_IDS)[number];

export function SettingsTabs({
  initialTab,
  userId,
  userEmail,
  org,
  role,
  profile,
  connection,
  usage,
  whiteLabel,
}: SettingsTabsProps) {
  const startTab: TabId = TAB_IDS.includes(initialTab as TabId) ? (initialTab as TabId) : "profile";
  const [tab, setTab] = useState<TabId>(startTab);
  const whiteLabelAllowed = can(org, "white_label");
  const isOwnerOrAdmin = role === "owner" || role === "admin";

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight">Settings</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Manage your profile, organization, WhatsApp connection, and team.
        </p>
      </div>

      <Tabs value={tab} onValueChange={(v) => setTab(v as TabId)}>
        <TabsList>
          <TabsTrigger value="profile">Profile</TabsTrigger>
          <TabsTrigger value="organization">Organization</TabsTrigger>
          <TabsTrigger value="whatsapp">WhatsApp connection</TabsTrigger>
          <TabsTrigger value="white-label">
            <span className="inline-flex items-center gap-1.5">
              White-label
              {!whiteLabelAllowed && <Lock className="h-3 w-3" />}
            </span>
          </TabsTrigger>
          <TabsTrigger value="team">Team</TabsTrigger>
        </TabsList>

        <div className="pt-5">
          <TabsContent value="profile">
            <ProfileTab userId={userId} userEmail={userEmail} initialProfile={profile} />
          </TabsContent>
          <TabsContent value="organization">
            <OrganizationTab org={org} usage={usage} canEdit={isOwnerOrAdmin} />
          </TabsContent>
          <TabsContent value="whatsapp">
            <WhatsAppTab initialConnection={connection} />
          </TabsContent>
          <TabsContent value="white-label">
            <WhiteLabelTab
              orgId={org.id}
              initialWhiteLabel={whiteLabel}
              allowed={whiteLabelAllowed}
              canEdit={isOwnerOrAdmin}
            />
          </TabsContent>
          <TabsContent value="team">
            <TeamTab orgId={org.id} isOwnerOrAdmin={isOwnerOrAdmin} />
          </TabsContent>
        </div>
      </Tabs>
    </div>
  );
}
