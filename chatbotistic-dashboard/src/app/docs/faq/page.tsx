import { DocArticle, DocH2, DocP } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "FAQ" };

const FAQS: { q: string; a: string }[] = [
  {
    q: "Do I need a Meta Business account?",
    a: "Only if you want to send Campaigns from this dashboard. If you're fine replying manually, connect your personal WhatsApp number instead — no Meta account needed.",
  },
  {
    q: "Why don't I see a status change reflected right away?",
    a: "Lead status changes (New/Contacted/Won/Lost) save immediately to the database. If a teammate is looking at a stale list, they may need to refresh — leads aren't pushed live between open browser tabs yet.",
  },
  {
    q: "Can I re-run a sync if I think leads are missing?",
    a: "Yes — the Leads page has a \"Sync now\" button that pulls the latest from Tochat on demand, in addition to however often it syncs automatically.",
  },
  {
    q: "What happens to a lead's status when it re-syncs?",
    a: "Syncing never overwrites a status you've already set. If a lead came back in as \"open\" from Tochat but you'd already marked it \"contacted\", your status sticks.",
  },
  {
    q: "Is my WhatsApp access token safe?",
    a: "Yes — it's encrypted (AES-256-GCM) on the server before being stored, and is never sent back to the browser after you save it. Only a masked placeholder shows in the field afterward.",
  },
  {
    q: "Can I have more than one organization?",
    a: "Not from a single signup today — each account gets one personal organization automatically. Team invites let other accounts join yours; there isn't a self-serve \"create a second org\" flow yet outside of Agency sub-accounts.",
  },
  {
    q: "What's the difference between a widget's agent and a team member?",
    a: "An \"agent\" is a WhatsApp number configured on Tochat that answers chats for a widget. A \"team member\" is a person with a login to this dashboard. They're unrelated — a dashboard team member doesn't need to also be a WhatsApp agent, and vice versa.",
  },
  {
    q: "My invite link doesn't work — what happened?",
    a: "Invite tokens are single-use and don't expire on a timer, but they do stop working once accepted, or if they were revoked by an admin. Ask whoever invited you to check Settings → Team → Pending invites and send a fresh one if needed.",
  },
];

export default function Page() {
  return (
    <DocArticle slug="faq" title="FAQ" description="Quick answers to the questions that come up most.">
      {FAQS.map((item) => (
        <div key={item.q}>
          <DocH2 className="mt-0 text-base">{item.q}</DocH2>
          <DocP className="mt-1">{item.a}</DocP>
        </div>
      ))}
    </DocArticle>
  );
}
