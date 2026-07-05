import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Connect WhatsApp" };

export default function Page() {
  return (
    <DocArticle
      slug="connect-whatsapp"
      title="Connect WhatsApp"
      description="Two ways to connect a WhatsApp number, in Settings → WhatsApp Connection."
    >
      <DocH2>Option 1 — Personal number (leads only)</DocH2>
      <DocP>
        The simplest option. Enter the WhatsApp number you already use, and that's it — leads still flow into
        the dashboard normally, but you reply to people from your own phone/WhatsApp app. There's nothing to
        authorize with Meta, and no message quota applies since sends don't go through this dashboard at all.
      </DocP>
      <DocUL>
        <li>Good for: solo operators, or anyone not ready to set up the Meta Cloud API yet.</li>
        <li>Limitation: you can't send bulk <strong>Campaigns</strong> from this dashboard with a personal
          connection — that requires Meta Cloud API mode.</li>
      </DocUL>

      <DocH2>Option 2 — Meta Cloud API</DocH2>
      <DocP>Needed if you want to send Campaigns from the dashboard. You'll need three things from your Meta
        Business/WhatsApp Business Platform account:</DocP>
      <DocOL>
        <li><strong>Phone number ID</strong> — from your WhatsApp Business Platform app in Meta's developer
          console.</li>
        <li><strong>WABA ID</strong> — your WhatsApp Business Account ID.</li>
        <li><strong>Access token</strong> — a permanent (or long-lived) token with messaging permissions.</li>
      </DocOL>
      <DocP>
        Paste these into Settings → WhatsApp Connection → Meta Cloud API and save. The access token is
        encrypted (AES-256-GCM) before it's stored — it's never sent back to your browser again after saving.
      </DocP>

      <DocCallout>
        The "Test connection" button checks that your saved credentials are present and well-formed. It's a
        sanity check, not a live call to Meta's Graph API — actually sending messages through the Cloud API
        isn't wired up in this build yet, so a "successful" test doesn't guarantee sends will go through.
      </DocCallout>

      <DocH2>Switching modes later</DocH2>
      <DocP>
        You can switch between Personal and Meta Cloud API at any time — saving a new mode replaces the old
        connection. Disconnecting clears any saved credentials.
      </DocP>
    </DocArticle>
  );
}
