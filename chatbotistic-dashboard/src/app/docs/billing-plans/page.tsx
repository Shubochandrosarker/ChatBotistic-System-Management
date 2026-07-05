import { DocArticle, DocCallout, DocH2, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Billing & Plans" };

export default function Page() {
  return (
    <DocArticle
      slug="billing-plans"
      title="Billing & Plans"
      description="What each plan includes, and where billing actually happens."
    >
      <DocH2>Plans at a glance</DocH2>
      <DocUL>
        <li><strong>Free</strong> — $0. 1 widget, 1 agent, 1 domain, 1 seat, 100 messages/mo. Widget shows
          Chatbotistic branding.</li>
        <li><strong>Starter</strong> — $19/mo. 3 widgets, 5 agents, 3 domains, 2 seats, 1,000 messages/mo.
          Adds booking forms and shared inbox.</li>
        <li><strong>Growth</strong> — $49/mo. 10 widgets, 20 agents, 10 domains, 5 seats, 5,000 messages/mo.
          Adds the landing page editor, AI-ready agents, branding removal, and lead attribution.</li>
        <li><strong>Agency</strong> — $149/mo. 30 widgets, unlimited agents, 50 domains, 15 seats, 25,000
          messages/mo, plus white-label and up to 10 sub-accounts.</li>
      </DocUL>

      <DocCallout>
        Every limit above is a default — your account may have individual overrides (entitlements) on top of
        the plan, which always take priority. Check Settings → Organization to see your actual current
        limits and usage.
      </DocCallout>

      <DocH2>Where billing happens</DocH2>
      <DocP>
        This dashboard doesn't process payments itself. Your plan, billing, and invoices are managed on{" "}
        <strong>chatbotistic.com</strong> (via Memberistic/Licenseistic) — when you upgrade or downgrade
        there, it syncs back to your organization here automatically the next time you sign in.
      </DocP>

      <DocH2>What happens if I hit a limit?</DocH2>
      <DocUL>
        <li><strong>Widgets/domains/agents/seats:</strong> the relevant "create" action is blocked with an
          upgrade prompt — nothing existing breaks.</li>
        <li><strong>Messages:</strong> Campaign sending is blocked once you've used the month's quota; drafts
          and scheduling still work, and the quota resets at the start of the next calendar month.</li>
      </DocUL>
    </DocArticle>
  );
}
