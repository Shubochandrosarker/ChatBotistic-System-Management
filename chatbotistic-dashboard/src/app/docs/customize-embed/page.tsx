import { DocArticle, DocCallout, DocCode, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Customize & Embed" };

export default function Page() {
  return (
    <DocArticle
      slug="customize-embed"
      title="Customize & Embed"
      description="Style your widget and drop it onto your site."
    >
      <DocH2>Customize</DocH2>
      <DocP>From a widget's settings you can adjust:</DocP>
      <DocUL>
        <li>Bubble color, position (bottom-left/bottom-right), and greeting text.</li>
        <li>Banners — proactive prompts shown before someone opens the chat.</li>
        <li>Booking configs — if you want visitors to be able to book a slot directly from the widget (see
          <em> Booking Forms</em>).</li>
      </DocUL>
      <DocP>
        On Free and Starter plans, the widget shows a small "Powered by Chatbotistic" mark. Growth and Agency
        plans remove it (the <DocCode>no_branding</DocCode> feature).
      </DocP>

      <DocH2>Embed the snippet</DocH2>
      <DocP>Every widget has a small install snippet — paste it before the closing <DocCode>&lt;/body&gt;</DocCode> tag on any page you want it to appear on:</DocP>
      <pre className="overflow-x-auto rounded-xl bg-muted/50 p-4 text-[13px] leading-relaxed">
        <code>{`<script src="https://cdn.tochat.be/widget.js" data-widget-id="YOUR_WIDGET_ID" async></script>`}</code>
      </pre>

      <DocOL>
        <li>Copy the snippet from the widget's Customize &amp; Embed tab (it already has your widget ID filled in).</li>
        <li>Paste it into your site's template/footer — WordPress, Webflow, a custom site, doesn't matter, as
          long as it loads on every page you want the bubble on.</li>
        <li>Reload the page — the bubble should appear within a couple of seconds.</li>
      </DocOL>

      <DocCallout>
        Nothing showing up? Check the widget's <strong>display rules</strong> first — a rule scoping it to a
        specific path or domain is the most common reason a freshly embedded widget doesn't appear.
      </DocCallout>
    </DocArticle>
  );
}
