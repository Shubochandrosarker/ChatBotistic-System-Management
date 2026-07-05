"use client";

import { motion } from "framer-motion";
import { useReducedMotion } from "@/components/motion/use-reduced-motion";

/** Animated progress bar for the Getting Started checklist. */
export function ChecklistProgress({ done, total }: { done: number; total: number }) {
  const reduced = useReducedMotion();
  const pct = total > 0 ? (done / total) * 100 : 0;

  return (
    <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
      {reduced ? (
        <div className="h-full rounded-full bg-primary" style={{ width: `${pct}%` }} />
      ) : (
        <motion.div
          className="h-full rounded-full bg-primary"
          initial={{ width: 0 }}
          animate={{ width: `${pct}%` }}
          transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
        />
      )}
    </div>
  );
}
