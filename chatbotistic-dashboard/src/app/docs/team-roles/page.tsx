import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Team & Roles" };

export default function Page() {
  return (
    <DocArticle
      slug="team-roles"
      title="Team & Roles"
      description="Roles, and how invites work today (there's no automatic invite email yet — here's the honest version)."
    >
      <DocH2>Roles</DocH2>
      <DocUL>
        <li><strong>Owner</strong> — the organization's creator. Can't be removed or demoted; there's always
          exactly one.</li>
        <li><strong>Admin</strong> — can manage organization settings, WhatsApp connection, white-label, and
          invite/remove teammates.</li>
        <li><strong>Agent</strong> — can use the dashboard (leads, campaigns) but can't change org-level
          settings or manage the team.</li>
      </DocUL>
      <DocP>Your plan caps total seats: Free 1, Starter 2, Growth 5, Agency 15.</DocP>

      <DocH2>Inviting a teammate</DocH2>
      <DocP>
        From Settings → Team, an owner/admin can create an invite (email + role). Be aware:{" "}
        <strong>there's no automated invite email yet</strong> — creating an invite gives you a link to copy
        and send yourself (Slack, email, however you'd normally reach that person).
      </DocP>
      <DocOL>
        <li>The invitee needs a Chatbotistic account first — if they don't have one, they sign up normally at
          the login page.</li>
        <li>Once signed in, they go to Settings → Team → "Accept an invite" and paste the token (or the whole
          link) you sent them.</li>
        <li>That adds them to your organization at the role you set when creating the invite.</li>
      </DocOL>

      <DocCallout>
        Pending invites show in Settings → Team with a "Copy link" button and a revoke option, so you can undo
        one before it's accepted.
      </DocCallout>

      <DocH2>Removing someone or changing their role</DocH2>
      <DocP>
        Owners/admins can change a member's role (Admin ↔ Agent) or remove them entirely from the same Team
        tab. The Owner role itself can't be reassigned or removed from here.
      </DocP>
    </DocArticle>
  );
}
