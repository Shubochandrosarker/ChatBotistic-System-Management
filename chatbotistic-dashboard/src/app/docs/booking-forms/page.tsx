import { DocArticle, DocCallout, DocH2, DocOL, DocP, DocUL } from "@/components/docs/doc-article";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Booking Forms" };

export default function Page() {
  return (
    <DocArticle
      slug="booking-forms"
      title="Booking Forms"
      description="Let a WhatsApp conversation end in a scheduled appointment, not just a chat."
    >
      <DocP>
        A booking config attaches a scheduling flow to one of your widget's agents. Instead of the chat
        ending in "someone will get back to you," the visitor picks a time slot right there, and it lands on
        their lead record as structured booking data.
      </DocP>

      <DocH2>Setting one up</DocH2>
      <DocOL>
        <li>Open the agent you want bookable, and add a booking config.</li>
        <li>Define your available slots — working hours, slot length, and how far in advance someone can
          book.</li>
        <li>Save. The agent's chat flow will now offer booking as an option during the conversation.</li>
      </DocOL>

      <DocCallout>
        Booking forms are gated behind the <strong>booking_forms</strong> feature — available on Starter and
        above. Free-plan orgs will see a locked upgrade prompt instead of the booking config option.
      </DocCallout>

      <DocH2>Where booking data shows up</DocH2>
      <DocUL>
        <li>Open the lead in <strong>Leads</strong> and expand the detail drawer — any booking made during that
          conversation appears in a dedicated "Booking" section (the raw <code>booking_data</code> your agent
          collected: time, notes, whatever fields you configured).</li>
        <li>There's no separate booking calendar view in this build — bookings live as part of the lead
          record they belong to.</li>
      </DocUL>
    </DocArticle>
  );
}
