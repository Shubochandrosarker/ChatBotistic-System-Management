"use client";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { StaggerItem, StaggerList } from "@/components/motion/stagger-list";
import { motion } from "framer-motion";
import { LayoutGrid, Plus } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { PLAN_ORDER, PLANS, withinLimit, type Limit, type OrgLike, type PlanId } from "@/lib/plans";
import { ConfirmDialog } from "./confirm-dialog";
import { EmbedDialog } from "./embed-dialog";
import { UpgradeDialog } from "./upgrade-dialog";
import { WidgetCard } from "./widget-card";
import type { WidgetCacheRow } from "./widget-form-types";

function nextPlanFor(org: OrgLike, limit: Limit) {
  const currentId = (org.plan ?? "free") as PlanId;
  const idx = PLAN_ORDER.indexOf(currentId);
  for (let i = idx + 1; i < PLAN_ORDER.length; i++) {
    const candidate = PLANS[PLAN_ORDER[i]];
    if (candidate.limits[limit] < 0 || candidate.limits[limit] > PLANS[currentId].limits[limit]) {
      return candidate;
    }
  }
  return null;
}

export function WidgetsList({
  org,
  initialWidgets,
}: {
  org: OrgLike;
  initialWidgets: WidgetCacheRow[];
}) {
  const router = useRouter();
  const [widgets, setWidgets] = useState(initialWidgets);
  const [busyId, setBusyId] = useState<string | null>(null);
  const [upgradeOpen, setUpgradeOpen] = useState(false);
  const [embedWidget, setEmbedWidget] = useState<WidgetCacheRow | null>(null);
  const [deleteWidget, setDeleteWidget] = useState<WidgetCacheRow | null>(null);
  const [error, setError] = useState<string | null>(null);

  const currentPlan = PLANS[(org.plan ?? "free") as PlanId] ?? PLANS.free;

  function handleCreateClick() {
    if (!withinLimit(org, "widgets", widgets.length)) {
      setUpgradeOpen(true);
      return;
    }
    router.push("/widgets/new");
  }

  async function toggleActive(row: WidgetCacheRow, next: boolean) {
    setBusyId(row.tochat_widget_id);
    setError(null);
    const previous = widgets;
    setWidgets((ws) =>
      ws.map((w) => (w.tochat_widget_id === row.tochat_widget_id ? { ...w, active: next } : w))
    );
    try {
      const res = await fetch(`/api/tochat/widgets/${encodeURIComponent(row.tochat_widget_id)}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ active: next }),
      });
      if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.error || "Failed to update widget.");
      }
    } catch (err) {
      setWidgets(previous);
      setError(err instanceof Error ? err.message : "Failed to update widget.");
    } finally {
      setBusyId(null);
    }
  }

  async function confirmDelete() {
    if (!deleteWidget) return;
    const row = deleteWidget;
    setError(null);
    try {
      const res = await fetch(`/api/tochat/widgets/${encodeURIComponent(row.tochat_widget_id)}`, {
        method: "DELETE",
      });
      if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.error || "Failed to delete widget.");
      }
      setWidgets((ws) => ws.filter((w) => w.tochat_widget_id !== row.tochat_widget_id));
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to delete widget.");
      throw err;
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Widgets</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Design and embed WhatsApp chat widgets across your sites.
          </p>
        </div>
        <Button type="button" variant="primary" onClick={handleCreateClick}>
          <Plus className="h-4 w-4" /> Create widget
        </Button>
      </div>

      {error && (
        <motion.div
          initial={{ opacity: 0, y: -6 }}
          animate={{ opacity: 1, y: 0 }}
          className="rounded-xl border border-destructive/30 bg-destructive/10 px-4 py-2.5 text-sm text-destructive"
        >
          {error}
        </motion.div>
      )}

      {widgets.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center gap-3 py-16 text-center">
            <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-accent text-accent-foreground">
              <LayoutGrid className="h-6 w-6" />
            </span>
            <div>
              <p className="text-sm font-semibold text-foreground">No widgets yet</p>
              <p className="mt-1 max-w-sm text-sm text-muted-foreground">
                Create your first WhatsApp chat widget to start turning site visitors into conversations.
              </p>
            </div>
            <Button type="button" variant="primary" onClick={handleCreateClick} className="mt-2">
              <Plus className="h-4 w-4" /> Create your first widget
            </Button>
          </CardContent>
        </Card>
      ) : (
        <StaggerList className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
          {widgets.map((w) => (
            <StaggerItem key={w.id}>
              <WidgetCard
                widget={w}
                busy={busyId === w.tochat_widget_id}
                onToggleActive={(next) => toggleActive(w, next)}
                onEmbed={() => setEmbedWidget(w)}
                onDelete={() => setDeleteWidget(w)}
              />
            </StaggerItem>
          ))}
        </StaggerList>
      )}

      <UpgradeDialog
        open={upgradeOpen}
        onOpenChange={setUpgradeOpen}
        limit="widgets"
        currentPlan={currentPlan}
        nextPlan={nextPlanFor(org, "widgets")}
        currentMax={currentPlan.limits.widgets}
      />

      {embedWidget && (
        <EmbedDialog
          open={!!embedWidget}
          onOpenChange={(open) => !open && setEmbedWidget(null)}
          widgetId={embedWidget.tochat_widget_id}
        />
      )}

      {deleteWidget && (
        <ConfirmDialog
          open={!!deleteWidget}
          onOpenChange={(open) => !open && setDeleteWidget(null)}
          title="Delete this widget?"
          description={`"${deleteWidget.name || "Untitled widget"}" will stop working on any site it's embedded on. This can't be undone.`}
          onConfirm={confirmDelete}
        />
      )}
    </div>
  );
}
