/**
 * Ordered doc sections — single source of truth for the sidebar nav
 * and the prev/next footer nav on each article. Add a page, add it
 * here, done.
 */
export interface DocSection {
  slug: string;
  title: string;
}

export const DOC_SECTIONS: DocSection[] = [
  { slug: "getting-started", title: "Getting Started" },
  { slug: "create-widget", title: "Create Your First Widget" },
  { slug: "customize-embed", title: "Customize & Embed" },
  { slug: "landing-pages", title: "Landing Pages" },
  { slug: "booking-forms", title: "Booking Forms" },
  { slug: "managing-leads", title: "Managing Leads" },
  { slug: "connect-whatsapp", title: "Connect WhatsApp" },
  { slug: "campaigns", title: "Campaigns & Message Limits" },
  { slug: "white-label", title: "White-Label Setup" },
  { slug: "team-roles", title: "Team & Roles" },
  { slug: "billing-plans", title: "Billing & Plans" },
  { slug: "faq", title: "FAQ" },
];

export function docHref(slug: string) {
  return `/docs/${slug}`;
}

export function neighbors(slug: string): { prev: DocSection | null; next: DocSection | null } {
  const idx = DOC_SECTIONS.findIndex((s) => s.slug === slug);
  if (idx === -1) return { prev: null, next: null };
  return {
    prev: idx > 0 ? DOC_SECTIONS[idx - 1] : null,
    next: idx < DOC_SECTIONS.length - 1 ? DOC_SECTIONS[idx + 1] : null,
  };
}
