"use client";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";
import { Tooltip } from "@/components/ui/tooltip";
import { HoverLift } from "@/components/motion/hover-lift";
import { cn } from "@/lib/utils";
import { ArrowLeftToLine, ArrowRightToLine, Code2, Pencil, Trash2 } from "lucide-react";
import Link from "next/link";
import type { WidgetCacheRow } from "./widget-form-types";

export function WidgetCard({
  widget,
  busy,
  onToggleActive,
  onEmbed,
  onDelete,
}: {
  widget: WidgetCacheRow;
  busy?: boolean;
  onToggleActive: (next: boolean) => void;
  onEmbed: () => void;
  onDelete: () => void;
}) {
  const settings = widget.settings ?? {};
  const color = (settings.color as string) || "#25D366";
  const position = (settings.position as string) === "left" ? "left" : "right";
  const name = widget.name || "Untitled widget";

  return (
    <HoverLift>
      <div className="flex h-full flex-col gap-4 rounded-2xl border border-border bg-card p-5 shadow-soft">
        <div className="flex items-start justify-between gap-3">
          <div className="flex min-w-0 items-center gap-2.5">
            <span
              aria-hidden
              className={cn(
                "mt-1 h-2 w-2 shrink-0 rounded-full",
                widget.active ? "bg-success" : "bg-muted-foreground/40"
              )}
            />
            <div className="min-w-0">
              <Link
                href={`/widgets/${widget.tochat_widget_id}`}
                className="block truncate text-sm font-semibold text-foreground hover:underline"
              >
                {name}
              </Link>
              <Badge variant={widget.active ? "success" : "outline"} className="mt-1">
                {widget.active ? "Active" : "Inactive"}
              </Badge>
            </div>
          </div>
          <Tooltip content={`Chat button on the ${position} side`}>
            <span
              aria-hidden
              className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white shadow-soft"
              style={{ backgroundColor: color }}
            >
              {position === "left" ? (
                <ArrowLeftToLine className="h-4 w-4" />
              ) : (
                <ArrowRightToLine className="h-4 w-4" />
              )}
            </span>
          </Tooltip>
        </div>

        <div className="flex flex-1 items-center justify-between gap-2 rounded-xl bg-muted/30 px-3 py-2">
          <span className="text-xs font-medium text-muted-foreground">Live on your site</span>
          <Switch
            checked={widget.active}
            onCheckedChange={onToggleActive}
            disabled={busy}
            aria-label={widget.active ? "Deactivate widget" : "Activate widget"}
          />
        </div>

        <div className="mt-auto flex items-center gap-2">
          <Link
            href={`/widgets/${widget.tochat_widget_id}`}
            className={cn(
              "inline-flex h-8 flex-1 select-none items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-border bg-transparent px-3 text-sm font-medium text-foreground",
              "transition-colors duration-150 ease-out hover:bg-muted",
              "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
            )}
          >
            <Pencil className="h-3.5 w-3.5" /> Edit
          </Link>
          <Tooltip content="Get embed script">
            <Button type="button" variant="outline" size="icon" onClick={onEmbed} aria-label="Embed widget">
              <Code2 className="h-4 w-4" />
            </Button>
          </Tooltip>
          <Tooltip content="Delete widget">
            <Button
              type="button"
              variant="outline"
              size="icon"
              onClick={onDelete}
              aria-label="Delete widget"
              className="text-destructive hover:bg-destructive/10"
            >
              <Trash2 className="h-4 w-4" />
            </Button>
          </Tooltip>
        </div>
      </div>
    </HoverLift>
  );
}
