import { DocArticle, DocCallout, DocH2, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Landing Pages" };

export default function Page() {
  return (
    <DocArticle
      slug="landing-pages"
      title="Landing Pages"
      description="A dedicated, shareable page that opens straight into a WhatsApp chat — no widget embed needed."
    >
      <DocP>
        Landing pages are useful when you want a link you can drop into an ad, a bio link, or a QR code —
        somewhere a full site embed doesn't make sense. Instead of a chat bubble sitting on your existing
        site, a landing page <em>is</em> the whole page: a visitor lands on it and is taken straight into a
        WhatsApp conversation with the widget's agent.
      </DocP>

      <DocH2>Setting one up</DocH2>
      <DocUL>
        <li>Pick which widget's agent(s) the landing page should route to.</li>
        <li>Choose the page's look — headline, description, and call-to-action button text.</li>
        <li>Publish — you'll get a shareable link (and, if you've generated one, a QR code) that opens the
          chat directly.</li>
      </DocUL>

      <DocCallout>
        Landing pages inherit whatever <strong>attribution</strong> data is available (referer, UTM params),
        which shows up on the resulting lead's detail view — handy for tying a specific ad or QR code back to
        real conversations. Requires the Growth plan or above.
      </DocCallout>

      <DocH2>Landing page vs. widget embed — which one?</DocH2>
      <DocP>
        Use a widget embed when you want the chat option available <em>alongside</em> your existing site
        content. Use a landing page when the chat itself is the destination — nothing else on the page
        matters.
      </DocP>
    </DocArticle>
  );
}
