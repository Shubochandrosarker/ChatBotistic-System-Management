"use client";

import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { RefreshCw } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";

/** Small client island for the dashboard's "Sync Leads" quick action —
 * the rest of the dashboard page is a server component. */
export function SyncLeadsButton() {
  const router = useRouter();
  const [syncing, setSyncing] = useState(false);

  async function sync() {
    setSyncing(true);
    try {
      await fetch("/api/leads/sync", { method: "POST" });
      router.refresh();
    } finally {
      setSyncing(false);
    }
  }

  return (
    <Button variant="outline" onClick={sync} loading={syncing}>
      <RefreshCw className={cn("h-4 w-4", syncing && "animate-spin")} />
      Sync leads
    </Button>
  );
}
