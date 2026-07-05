import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Your First Widget" };

export default function Page() {
  return (
    <DocArticle
      slug="create-widget"
      title="Create Your First Widget"
      description="Widgets are the chat bubble your visitors see — here's how to set one up."
    >
      <DocP>
        Head to <strong>Widgets</strong> in the sidebar and click <strong>Create widget</strong>. Every widget
        belongs to your organization and is scoped to a domain — pick which site it should appear on before
        you go further.
      </DocP>

      <DocH2>What you'll set up</DocH2>
      <DocOL>
        <li><strong>Name</strong> — internal only, for your own reference (e.g. "Main site — support").</li>
        <li><strong>Agent(s)</strong> — the WhatsApp number(s) that answer chats from this widget. A widget
          needs at least one agent before it's useful.</li>
        <li><strong>Display rules</strong> — control when/where the bubble shows (all pages, specific paths,
          after a delay, etc.).</li>
        <li><strong>Banners</strong> — optional proactive messages that pop up before a visitor even clicks the
          bubble.</li>
      </DocOL>

      <DocCallout>
        Your plan caps the number of widgets you can create (Free: 1, Starter: 3, Growth: 10, Agency: 30). If
        you're at your limit, the create button will tell you to upgrade — see <em>Billing &amp; Plans</em>.
      </DocCallout>

      <DocH2>Next step</DocH2>
      <DocUL>
        <li>Once the widget exists, add at least one <strong>agent</strong> (a WhatsApp number) so it can
          actually receive messages.</li>
        <li>Then head to <em>Customize &amp; Embed</em> to style it and grab the install snippet.</li>
      </DocUL>
    </DocArticle>
  );
}
