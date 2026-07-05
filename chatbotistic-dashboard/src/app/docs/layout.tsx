import { DocsNav } from "@/components/docs/docs-nav";
import { MessageSquareText } from "lucide-react";
import Link from "next/link";
import type { Metadata } from "next";

/**
 * Public docs shell — deliberately OUTSIDE the `(dashboard)` route
 * group and its auth gate.
 *
 * Placement decision: these pages are linked from the marketing site
 * (`cb_dashboard_url('docs')`) for prospects who don't have an account
 * yet, and they're genuinely useful to read logged-out (pricing/plan
 * questions, "how does WhatsApp connection work" before signing up).
 * Nesting under `(dashboard)` would force every doc page through
 * `DashboardLayout`'s `redirect("/login")` gate, which is wrong for
 * that audience. Route groups don't affect the URL anyway — `/docs`
 * resolves to whichever `src/app/**` folder defines it, dashboard
 * group or not — so this just needed to live at the top level instead
 * of `src/app/(dashboard)/docs/`. Logged-in users clicking "Docs" in
 * the sidebar land here too, outside the dashboard chrome; that's
 * fine, it's still one click back via the header link below.
 */
export const metadata: Metadata = {
  title: {
    default: "Docs",
    template: "%s — Chatbotistic Docs",
  },
  description: "Guides for setting up widgets, WhatsApp, campaigns, and your team on Chatbotistic.",
};

export default function DocsLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="mx-auto flex min-h-screen max-w-6xl flex-col px-4 sm:px-6">
      <header className="flex h-16 shrink-0 items-center justify-between border-b border-border">
        <Link href="/dashboard" className="flex items-center gap-2.5">
          <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-primary text-primary-foreground">
            <MessageSquareText className="h-[18px] w-[18px]" />
          </span>
          <span className="text-[15px] font-semibold tracking-tight">Chatbotistic Docs</span>
        </Link>
        <Link
          href="/dashboard"
          className="rounded-xl px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
        >
          Go to dashboard →
        </Link>
      </header>

      <div className="flex flex-1 flex-col gap-8 py-8 lg:flex-row">
        <aside className="shrink-0 lg:w-56">
          <div className="lg:sticky lg:top-8">
            <DocsNav />
          </div>
        </aside>
        <main className="min-w-0 flex-1 pb-16">{children}</main>
      </div>
    </div>
  );
}
