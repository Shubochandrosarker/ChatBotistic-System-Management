import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "White-Label Setup" };

export default function Page() {
  return (
    <DocArticle
      slug="white-label"
      title="White-Label Setup"
      description="Rebrand the dashboard and widget for client portfolios — Agency plan."
    >
      <DocCallout>
        White-label is available on the <strong>Agency</strong> plan only. On any other plan, the
        Settings → White-Label tab shows a locked upgrade prompt instead of the form below.
      </DocCallout>

      <DocH2>What you can customize</DocH2>
      <DocUL>
        <li><strong>Brand name</strong> — replaces "Chatbotistic" in places your clients see.</li>
        <li><strong>Primary color</strong> — used across the widget and (where applicable) the dashboard
          accent color.</li>
        <li><strong>Email sender name</strong> — the "from" name on any transactional emails sent for your
          org.</li>
        <li><strong>Custom domain</strong> — point your own subdomain at the dashboard instead of using
          Chatbotistic's.</li>
      </DocUL>

      <DocH2>Setting up a custom domain</DocH2>
      <DocOL>
        <li>Pick a subdomain you control, e.g. <code>chat.yourbrand.com</code>.</li>
        <li>Add a CNAME record pointing it at <code>dashboard.chatbotistic.com</code>.</li>
        <li>Enter that subdomain into the Custom Domain field and save.</li>
        <li>DNS propagation can take up to 24 hours; SSL is issued automatically once the domain resolves.</li>
      </DocOL>

      <DocP>
        Everything here is saved into your organization's white-label settings and takes effect immediately
        once saved (aside from DNS propagation for a custom domain).
      </DocP>
    </DocArticle>
  );
}
