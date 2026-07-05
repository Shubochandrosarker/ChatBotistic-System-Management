"use client";

import { Button } from "@/components/ui/button";
import { Dialog, DialogBody, DialogFooter, DialogHeader } from "@/components/ui/dialog";
import { motion } from "framer-motion";
import { Sparkles } from "lucide-react";
import { useRouter } from "next/navigation";
import type { Limit, PlanDef } from "@/lib/plans";

const LIMIT_LABELS: Record<Limit, string> = {
  widgets: "widgets",
  agents: "agents",
  domains: "domains",
  seats: "seats",
  messages_per_month: "messages per month",
  sub_accounts: "sub-accounts",
};

export function UpgradeDialog({
  open,
  onOpenChange,
  limit,
  currentPlan,
  nextPlan,
  currentMax,
}: {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  limit: Limit;
  currentPlan: PlanDef;
  /** The next plan up that raises (or removes) this limit. */
  nextPlan: PlanDef | null;
  currentMax: number;
}) {
  const label = LIMIT_LABELS[limit];
  const router = useRouter();

  return (
    <Dialog open={open} onOpenChange={onOpenChange} className="max-w-md">
      <DialogHeader onClose={() => onOpenChange(false)}>You&apos;ve hit your {label} limit</DialogHeader>
      <DialogBody className="flex flex-col gap-4">
        <motion.div
          initial={{ opacity: 0, y: 6 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ type: "spring", stiffness: 380, damping: 30 }}
          className="flex items-start gap-3 rounded-xl border border-border bg-muted/30 p-4"
        >
          <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary">
            <Sparkles className="h-4 w-4" />
          </span>
          <div className="text-sm text-foreground">
            <p>
              The <span className="font-semibold">{currentPlan.name}</span> plan includes up to{" "}
              <span className="font-semibold">
                {currentMax < 0 ? "unlimited" : currentMax}
              </span>{" "}
              {label}.
            </p>
            {nextPlan ? (
              <p className="mt-1.5 text-muted-foreground">
                Upgrade to <span className="font-medium text-foreground">{nextPlan.name}</span> for up to{" "}
                {nextPlan.limits[limit] < 0 ? "unlimited" : nextPlan.limits[limit]} {label} — starting at $
                {nextPlan.priceMonthly}/mo.
              </p>
            ) : (
              <p className="mt-1.5 text-muted-foreground">
                Talk to us about a custom plan to raise this limit.
              </p>
            )}
          </div>
        </motion.div>
      </DialogBody>
      <DialogFooter>
        <Button type="button" variant="ghost" onClick={() => onOpenChange(false)}>
          Not now
        </Button>
        <Button
          type="button"
          variant="primary"
          onClick={() => {
            onOpenChange(false);
            router.push("/settings");
          }}
        >
          View plans
        </Button>
      </DialogFooter>
    </Dialog>
  );
}
