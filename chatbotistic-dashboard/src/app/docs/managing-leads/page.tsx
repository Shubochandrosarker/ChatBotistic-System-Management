import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Managing Leads" };

export default function Page() {
  return (
    <DocArticle
      slug="managing-leads"
      title="Managing Leads"
      description="Every visitor who starts a chat becomes a lead — here's how to triage them."
    >
      <DocP>
        The <strong>Leads</strong> page lists everyone who's messaged one of your widgets, pulled in from
        Tochat and kept in sync. Each lead carries their phone, name, country, referer/attribution info, which
        widget and agent they talked to, any custom form fields they filled in, and booking data if they
        scheduled something.
      </DocP>

      <DocH2>The status pipeline</DocH2>
      <DocP>Every lead moves through four states:</DocP>
      <DocUL>
        <li><strong>New</strong> — just came in, not yet triaged.</li>
        <li><strong>Contacted</strong> — you've followed up.</li>
        <li><strong>Won</strong> — became a customer/booking/whatever counts as a win for you.</li>
        <li><strong>Lost</strong> — didn't convert.</li>
      </DocUL>
      <DocP>
        Click the status chip on any row (or in the lead's detail view) to move it forward — New → Contacted →
        Won/Lost. You can reopen a Won/Lost lead if you need to revisit it.
      </DocP>

      <DocH2>Search, filter, and export</DocH2>
      <DocOL>
        <li>Search by name or phone number.</li>
        <li>Filter by widget, status, a date range, or toggle "New only" to see just what needs triage.</li>
        <li>Click <strong>Export CSV</strong> to download exactly the filtered rows you're looking at.</li>
        <li>Click <strong>Sync now</strong> to pull the latest leads from Tochat on demand, instead of waiting.</li>
      </DocOL>

      <DocCallout>
        Every lead's detail view has an <strong>Open WhatsApp chat</strong> link that jumps straight into
        <code> wa.me</code> with their number pre-filled — the fastest way to actually reply.
      </DocCallout>
    </DocArticle>
  );
}
