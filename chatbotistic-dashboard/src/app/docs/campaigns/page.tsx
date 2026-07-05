import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Campaigns & Message Limits" };

export default function Page() {
  return (
    <DocArticle
      slug="campaigns"
      title="Campaigns & Message Limits"
      description="Send one message to a filtered slice of your leads, and understand your monthly quota."
    >
      <DocH2>Creating a campaign</DocH2>
      <DocOL>
        <li>Go to <strong>Campaigns</strong> and click <strong>New campaign</strong>.</li>
        <li>Name it, then write the message body. Use the <code>{"{{name}}"}</code>, <code>{"{{phone}}"}</code>,
          and <code>{"{{widget}}"}</code> chips to insert per-lead variables.</li>
        <li>Pick your audience — all leads, leads from one widget, or leads at a specific status (e.g. only
          "New" leads). The recipient count updates live.</li>
        <li>Choose send now or schedule for later.</li>
      </DocOL>

      <DocH2>The usage meter</DocH2>
      <DocP>
        The bar at the top of the Campaigns page (and on your Dashboard home) tracks
        <code> message_usage</code> for the current calendar month against your plan's limit — it turns amber
        past 70% and red past 90%. Limits by plan: Free 100/mo, Starter 1,000/mo, Growth 5,000/mo, Agency
        25,000/mo.
      </DocP>

      <DocCallout>
        Sending is gated on two things: a <strong>connected WhatsApp connection</strong> (Meta Cloud API mode
        — see <em>Connect WhatsApp</em>) and <strong>remaining quota</strong> for the month. Missing either one
        turns "Send now" into a draft-only save with an explanation, rather than silently failing.
      </DocCallout>

      <DocH2>What actually happens on send</DocH2>
      <DocP>
        Today, sending marks the campaign as "sending" and is where the actual per-message delivery (via the
        Meta Cloud API) and quota increment would happen — that delivery loop isn't implemented yet in this
        build. Draft and Scheduled campaigns work fully; live delivery is on the roadmap.
      </DocP>

      <DocH2>Looking for Tochat's own campaigns?</DocH2>
      <DocUL>
        <li>Tochat.be has its own broadcast/drip campaign concept, separate from what's described here — this
          dashboard doesn't currently proxy or display that data.</li>
      </DocUL>
    </DocArticle>
  );
}
