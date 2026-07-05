import type { TochatFaqGroup, TochatOperator, TochatResource, TochatWidget } from "@/lib/tochat/types";

/** Row shape of the `widgets_cache` table (see supabase/migrations/003_*). */
export interface WidgetCacheRow {
  id: string;
  org_id: string;
  tochat_widget_id: string;
  name: string | null;
  settings: TochatWidget | null;
  active: boolean;
  synced_at: string;
}

/**
 * Editable widget shape shared by the editor form and the live
 * preview. A subset of `TochatWidget` — string fields default to ""
 * (not undefined) so controlled inputs never warn, and every default
 * mirrors what a brand-new widget should look like.
 */
export interface WidgetFormState {
  name: string;
  legend: string;
  widgetMessage: string;
  buttonMessage: string;
  offlineMessage: string;
  color: string;
  landingPrimaryColor: string;
  landingSecondaryColor: string;
  theme: number;
  position: "left" | "right";
  autoOpen: boolean;
  active: boolean;
}

export const DEFAULT_WIDGET_FORM: WidgetFormState = {
  name: "",
  legend: "",
  widgetMessage: "Hi there! How can we help you today?",
  buttonMessage: "Start chat",
  offlineMessage: "We're offline right now — leave us a message and we'll reply soon.",
  color: "#25D366",
  landingPrimaryColor: "#25D366",
  landingSecondaryColor: "#128C7E",
  theme: 1,
  position: "right",
  autoOpen: false,
  active: true,
};

/** Local editor row for an agent — carries the operator id once persisted. */
export interface AgentDraft {
  /** Tochat operator id once saved; undefined for a not-yet-created row. */
  id?: string;
  name: string;
  number: string;
  jobTitle: string;
  greeting: string;
  faqs: FaqDraft[];
  /** UI-only key for stable React lists before an id exists. */
  _key: string;
}

export interface FaqDraft {
  /** Tochat FAQ id once saved; undefined for a not-yet-created row. */
  id?: string;
  question: string;
  answer: string;
  _key: string;
}

let keySeq = 0;
export function nextKey(prefix: string): string {
  keySeq += 1;
  return `${prefix}-${keySeq}-${Date.now().toString(36)}`;
}

/**
 * Client-safe twin of `resourceId` from `@/lib/tochat/client` (that
 * module is `server-only`, so it can't be imported here) — extracts a
 * plain id from an `id`/`uuid` field or a Hydra `@id` IRI.
 */
export function extractId(resource: TochatResource): string {
  if (resource.uuid) return String(resource.uuid);
  if (resource.id) return String(resource.id);
  if (resource["@id"]) {
    const iri = String(resource["@id"]).replace(/\/$/, "");
    return iri.slice(iri.lastIndexOf("/") + 1);
  }
  return "";
}

export function operatorToDraft(op: TochatOperator, faqs: TochatFaqGroup[] = []): AgentDraft {
  return {
    id: extractId(op),
    name: op.name ?? "",
    number: op.number ?? "",
    jobTitle: op.jobTitle ?? "",
    greeting: op.greeting ?? "",
    faqs: faqs.map(faqToDraft),
    _key: nextKey("agent"),
  };
}

/** Map a `TochatWidget` (from the cache row's `settings` or a live fetch) into editable form state. */
export function widgetToFormState(widget: TochatWidget | null | undefined): WidgetFormState {
  if (!widget) return { ...DEFAULT_WIDGET_FORM };
  return {
    name: widget.name ?? DEFAULT_WIDGET_FORM.name,
    legend: widget.legend ?? DEFAULT_WIDGET_FORM.legend,
    widgetMessage: widget.widgetMessage ?? DEFAULT_WIDGET_FORM.widgetMessage,
    buttonMessage: widget.buttonMessage ?? DEFAULT_WIDGET_FORM.buttonMessage,
    offlineMessage: widget.offlineMessage ?? DEFAULT_WIDGET_FORM.offlineMessage,
    color: widget.color ?? DEFAULT_WIDGET_FORM.color,
    landingPrimaryColor: widget.landingPrimaryColor ?? DEFAULT_WIDGET_FORM.landingPrimaryColor,
    landingSecondaryColor: widget.landingSecondaryColor ?? DEFAULT_WIDGET_FORM.landingSecondaryColor,
    theme: typeof widget.theme === "number" ? widget.theme : DEFAULT_WIDGET_FORM.theme,
    position: widget.position === "left" ? "left" : "right",
    autoOpen: widget.autoOpen ?? DEFAULT_WIDGET_FORM.autoOpen,
    active: widget.active ?? DEFAULT_WIDGET_FORM.active,
  };
}

export function faqToDraft(f: TochatFaqGroup): FaqDraft {
  return {
    id: extractId(f),
    question: f.question ?? "",
    answer: f.answer ?? "",
    _key: nextKey("faq"),
  };
}
