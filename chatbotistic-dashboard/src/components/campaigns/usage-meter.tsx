"use client";

import { Card, CardContent } from "@/components/ui/card";
import { cn, formatNumber } from "@/lib/utils";
import { motion } from "framer-motion";
import { useReducedMotion } from "@/components/motion/use-reduced-motion";

/**
 * Message-quota usage meter — animated bar, green → amber → red as it
 * approaches the plan limit. Used at the top of /campaigns and reused
 * as a stat card on /dashboard, so it takes no data-fetching
 * dependency of its own: callers pass `used`/`limit` in.
 */
export function UsageMeter({
  used,
  limit,
  title = "Messages this month",
  compact = false,
}: {
  used: number;
  limit: number;
  title?: string;
  compact?: boolean;
}) {
  const reduced = useReducedMotion();
  const unlimited = limit < 0;
  const pct = unlimited ? 0 : Math.min(100, (used / Math.max(limit, 1)) * 100);

  const color = unlimited
    ? "bg-primary"
    : pct >= 90
      ? "bg-destructive"
      : pct >= 70
        ? "bg-warning"
        : "bg-primary";

  const body = (
    <div className="flex flex-col gap-2">
      <div className="flex items-baseline justify-between">
        <p className="text-sm font-medium text-muted-foreground">{title}</p>
        <p className="text-sm font-semibold text-foreground">
          {formatNumber(used)} {unlimited ? "" : `/ ${formatNumber(limit)}`}
          {unlimited && <span className="ml-1 font-normal text-muted-foreground">(unlimited)</span>}
        </p>
      </div>
      <div className="h-2.5 w-full overflow-hidden rounded-full bg-muted">
        {reduced ? (
          <div className={cn("h-full rounded-full", color)} style={{ width: `${unlimited ? 100 : pct}%` }} />
        ) : (
          <motion.div
            className={cn("h-full rounded-full", color)}
            initial={{ width: 0 }}
            animate={{ width: `${unlimited ? 100 : pct}%` }}
            transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
          />
        )}
      </div>
      {!unlimited && pct >= 90 && (
        <p className="text-xs text-destructive">
          You&apos;re almost at your monthly message limit — upgrade to keep sending.
        </p>
      )}
      {!unlimited && pct >= 70 && pct < 90 && (
        <p className="text-xs text-warning">Approaching your monthly message limit.</p>
      )}
    </div>
  );

  if (compact) return body;

  return (
    <Card>
      <CardContent className="pt-5">{body}</CardContent>
    </Card>
  );
}
