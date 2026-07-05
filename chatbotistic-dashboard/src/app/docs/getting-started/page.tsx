import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Getting Started" };

export default function Page() {
  return (
    <DocArticle
      slug="getting-started"
      title="Getting Started"
      description="What Chatbotistic does, and the fastest path from signup to your first WhatsApp lead."
    >
      <DocP>
        Chatbotistic embeds a WhatsApp chat widget on your site, captures every visitor who starts a
        conversation as a lead in your dashboard, and lets you follow up — either one-on-one or with bulk
        campaigns — without leaving the browser. Under the hood, the widget itself is powered by Tochat.be;
        this dashboard is where you configure it, see who's talking to you, and manage your account.
      </DocP>

      <DocH2>The five-minute setup</DocH2>
      <DocOL>
        <li>
          <strong>Create a widget</strong> — your first chat bubble, scoped to one site or domain. Free plans
          get 1 widget; paid plans get more (see <em>Billing &amp; Plans</em>).
        </li>
        <li>
          <strong>Add an agent</strong> — a WhatsApp number that receives and answers chats. You can add FAQ
          groups and booking configs to an agent later.
        </li>
        <li>
          <strong>Embed the widget</strong> on your site with the snippet from the widget's Customize &amp;
          Embed tab.
        </li>
        <li>
          <strong>Connect WhatsApp</strong> in Settings — either your personal number (manual replies) or the
          Meta Cloud API (needed to send Campaigns).
        </li>
        <li>
          <strong>Watch leads roll in</strong> on the Leads page, and follow up with a Campaign once you've
          got a few.
        </li>
      </DocOL>

      <DocCallout>
        The Dashboard home page has a live "Getting Started" checklist that tracks these same five steps
        against your actual account data — use it to see what's left.
      </DocCallout>

      <DocH2>How the pieces fit together</DocH2>
      <DocUL>
        <li><strong>Widgets</strong> — the embeddable chat bubble; each has its own agents, rules, and banners.</li>
        <li><strong>Leads</strong> — every visitor who starts a chat or fills a form, synced from Tochat into your
          local Leads table so you can triage, filter, and export them.</li>
        <li><strong>Campaigns</strong> — bulk WhatsApp sends to a filtered slice of your leads, metered against
          your plan's monthly message quota.</li>
        <li><strong>Settings</strong> — your profile, organization, WhatsApp connection, white-label (Agency
          plan), and team.</li>
      </DocUL>
    </DocArticle>
  );
}
