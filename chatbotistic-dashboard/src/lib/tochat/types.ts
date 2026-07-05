/**
 * Tochat.be (ChatWith) API resource shapes.
 *
 * The API is API-Platform/Hydra flavored: collections arrive as
 * `{ "hydra:member": [...] }`, resources carry `@id` IRIs, and writes
 * accept plain JSON (PATCH uses merge-patch+json).
 */

export interface HydraCollection<T> {
  "hydra:member"?: T[];
  "hydra:totalItems"?: number;
  [key: string]: unknown;
}

export interface TochatResource {
  "@id"?: string;
  id?: string;
  [key: string]: unknown;
}

/** A widget ("business" in Tochat's data model). */
export interface TochatWidget extends TochatResource {
  uuid?: string;
  name?: string;
  legend?: string;
  /** Embed key used in the load.js script URL. */
  key?: string;
  color?: string;
  landingPrimaryColor?: string;
  landingSecondaryColor?: string;
  theme?: number;
  position?: "left" | "right";
  widgetMessage?: string;
  buttonMessage?: string;
  offlineMessage?: string;
  autoOpen?: boolean;
  active?: boolean;
  /** Tenancy tag — every org maps to `org-{orgId}`. */
  userClient?: string;
  whatsapps?: TochatOperator[] | string[];
  createdAt?: string;
  updatedAt?: string;
}

/** WhatsApp operator (agent) attached to a widget. */
export interface TochatOperator extends TochatResource {
  uuid?: string;
  name?: string;
  number?: string;
  jobTitle?: string;
  greeting?: string;
  /** Booking form schema. */
  form?: { items?: TochatFormItem[] } | null;
  /** IRI of the owning widget, or embedded object. */
  business?: string | TochatWidget;
  active?: boolean;
}

export interface TochatFormItem {
  type: "text" | "email" | "tel" | "url" | "number" | "checkbox";
  label: string;
  required?: boolean;
}

export interface TochatFaqGroup extends TochatResource {
  question?: string;
  answer?: string;
  /** IRI of the owning operator. */
  whatsapp?: string | TochatOperator;
  position?: number;
}

/** A lead / interaction row from /api/v2/stats. */
export interface TochatStat extends TochatResource {
  uuid?: string;
  phone?: string;
  name?: string;
  email?: string;
  country?: string;
  referer?: string;
  url?: string;
  agent?: string;
  fields?: Record<string, unknown> | null;
  bookingData?: Record<string, unknown> | null;
  business?: string | TochatWidget;
  whatsapp?: string | TochatOperator;
  createdAt?: string;
}

export interface TochatStatsGraphPoint {
  date?: string;
  visits?: number;
  clicks?: number;
  leads?: number;
  [key: string]: unknown;
}

export interface TochatLandingLink extends TochatResource {
  url?: string;
  slug?: string;
  title?: string;
  [key: string]: unknown;
}

export interface TochatCampaign extends TochatResource {
  uuid?: string;
  name?: string;
  message?: string;
  status?: string;
  scheduledAt?: string;
  business?: string | TochatWidget;
  createdAt?: string;
}

export interface TochatManyContact extends TochatResource {
  name?: string;
  contacts?: unknown[];
  business?: string | TochatWidget;
}

export interface TochatBookingConfig extends TochatResource {
  whatsapp?: string | TochatOperator;
  slotMinutes?: number;
  timezone?: string;
  availability?: Record<string, unknown> | null;
}

export interface TochatBanner extends TochatResource {
  business?: string | TochatWidget;
  text?: string;
  image?: string;
  active?: boolean;
}

export interface TochatWidgetRule extends TochatResource {
  widget?: string | TochatWidget;
  type?: string;
  value?: string;
}

/** Row shape from /api/get-json-lead (lead-export API key endpoint). */
export interface TochatExportedLead {
  id?: string | number;
  phone?: string;
  name?: string;
  country?: string;
  referer?: string;
  widget?: string;
  agent?: string;
  createdAt?: string;
  [key: string]: unknown;
}

export class TochatApiError extends Error {
  status: number;
  constructor(message: string, status: number) {
    super(message);
    this.name = "TochatApiError";
    this.status = status;
  }
}
