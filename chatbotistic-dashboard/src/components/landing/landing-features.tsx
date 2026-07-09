"use client";

import { Card } from "@/components/ui/card";
import { motion } from "framer-motion";
import {
  CalendarCheck,
  Link2,
  Megaphone,
  MessageSquareText,
  Tags,
  Users,
} from "lucide-react";
import Link from "next/link";

// Mirrors src/components/docs/docs-config.ts — six of the real,
// documented product areas, each linking straight to its doc article
// rather than re-describing features here in a second place that
// could drift out of sync.
const FEATURES = [
  {
    href: "/docs/create-widget",
    icon: MessageSquareText,
    title: "Chat widgets",
    description:
      "An embeddable WhatsApp chat bubble for your site, styled to match your brand.",
  },
  {
    href: "/docs/managing-leads",
    icon: Users,
    title: "Leads",
    description:
      "Every visitor who starts a chat, synced from Tochat with status, filters, and CSV export.",
  },
  {
    href: "/docs/campaigns",
    icon: Megaphone,
    title: "Campaigns",
    description:
      "Bulk WhatsApp sends to a filtered slice of your leads, metered against your plan's quota.",
  },
  {
    href: "/docs/booking-forms",
    icon: CalendarCheck,
    title: "Booking forms",
    description:
      "Let a conversation end in a scheduled appointment, captured on the lead record.",
  },
  {
    href: "/docs/landing-pages",
    icon: Link2,
    title: "Landing pages",
    description:
      "A shareable link or QR code that opens straight into a WhatsApp chat.",
  },
  {
    href: "/docs/white-label",
    icon: Tags,
    title: "White-label",
    description:
      "Your own brand and domain on the dashboard and widget — Agency plan.",
  },
] as const;

export function LandingFeatures() {
  return (
    <section className="mx-auto max-w-6xl px-4 pb-24 sm:px-6">
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {FEATURES.map((feature, i) => (
          <motion.div
            key={feature.href}
            initial={{ opacity: 0, y: 14 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-40px" }}
            transition={{ duration: 0.4, delay: (i % 3) * 0.06, ease: "easeOut" }}
          >
            <Link href={feature.href}>
              <Card className="group h-full p-5 transition-shadow hover:shadow-lift">
                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary transition-transform group-hover:scale-105">
                  <feature.icon className="h-5 w-5" aria-hidden />
                </span>
                <h3 className="mt-4 font-semibold">{feature.title}</h3>
                <p className="mt-1.5 text-sm text-muted-foreground">
                  {feature.description}
                </p>
              </Card>
            </Link>
          </motion.div>
        ))}
      </div>
    </section>
  );
}
