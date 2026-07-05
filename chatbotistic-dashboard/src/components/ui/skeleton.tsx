import { cn } from "@/lib/utils";
import type { HTMLAttributes } from "react";

/** Shimmering placeholder block — `.cb-skeleton` keyframes live in globals.css. */
export function Skeleton({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn("cb-skeleton rounded-xl", className)}
      aria-hidden
      {...props}
    />
  );
}
