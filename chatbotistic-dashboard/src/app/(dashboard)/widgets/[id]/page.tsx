import { WidgetEditor } from "@/components/widgets/widget-editor";
import {
  operatorToDraft,
  widgetToFormState,
  type AgentDraft,
} from "@/components/widgets/widget-form-types";
import { listFaqs, operatorsForWidget, resourceId } from "@/lib/tochat/client";
import type { TochatWidget } from "@/lib/tochat/types";
import { OrgContextError, requireOrgContext } from "@/lib/org-context";
import type { Metadata } from "next";
import { notFound, redirect } from "next/navigation";

export const metadata: Metadata = {
  title: "Edit widget",
};

/** Resolve the org context or bail via redirect/notFound — never returns on failure. */
async function resolveCtxOrBail() {
  try {
    return await requireOrgContext();
  } catch (err) {
    if (err instanceof OrgContextError && err.status === 401) {
      redirect("/login");
    }
    notFound();
  }
}

export default async function EditWidgetPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const ctx = await resolveCtxOrBail();

  // Defense in depth, mirroring src/app/api/tochat/widgets/[id]/route.ts:
  // a cache row for this id under a *different* org 404s immediately.
  const { data: otherOrgRow } = await ctx.supabase
    .from("widgets_cache")
    .select("org_id")
    .eq("tochat_widget_id", id)
    .neq("org_id", ctx.org.id)
    .maybeSingle();
  if (otherOrgRow) {
    notFound();
  }

  const { data: cacheRow } = await ctx.supabase
    .from("widgets_cache")
    .select("*")
    .eq("org_id", ctx.org.id)
    .eq("tochat_widget_id", id)
    .maybeSingle();

  let widget: TochatWidget | null = (cacheRow?.settings as TochatWidget | undefined) ?? null;
  let agents: AgentDraft[] = [];

  try {
    const operators = await operatorsForWidget(id, ctx.userClient);
    if (!widget) {
      // Cache miss (e.g. never synced) — operatorsForWidget already
      // proved ownership via ownedWidget, so fall back to whatever
      // minimal shape we can render; the form still saves fine.
      widget = { uuid: id } as TochatWidget;
    }
    const withFaqs = await Promise.all(
      operators.map(async (op) => {
        const opId = resourceId(op);
        const faqs = opId ? await listFaqs(opId, ctx.userClient) : [];
        return operatorToDraft(op, faqs);
      })
    );
    agents = withFaqs;
  } catch {
    // Widget genuinely doesn't exist / isn't owned by this org.
    if (!widget) notFound();
  }

  return (
    <WidgetEditor
      mode="edit"
      widgetId={id}
      initialForm={widgetToFormState(widget)}
      initialAgents={agents}
    />
  );
}
