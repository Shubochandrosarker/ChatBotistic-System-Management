"use client";

import { Button } from "@/components/ui/button";
import { ThemeToggle } from "@/components/ui/theme-toggle";
import { motion } from "framer-motion";
import { ArrowRight, MessageSquareText } from "lucide-react";
import Link from "next/link";

/**
 * App-root welcome page. chatbotistic.com owns the full marketing
 * pitch and pricing (this page links out to it rather than
 * duplicating a pricing table that could drift) — this is a branded
 * gateway into the product for direct visits and "Open Dashboard"
 * links, whether or not the visitor already has a session.
 */
export function LandingHero() {
  return (
    <header className="relative overflow-hidden">
      <motion.div
        aria-hidden
        className="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary/20 blur-3xl"
        animate={{ x: [0, 30, 0], y: [0, 20, 0] }}
        transition={{ duration: 16, repeat: Infinity, ease: "easeInOut" }}
      />
      <motion.div
        aria-hidden
        className="pointer-events-none absolute -right-24 top-24 h-80 w-80 rounded-full bg-primary/10 blur-3xl"
        animate={{ x: [0, -24, 0], y: [0, 24, 0] }}
        transition={{ duration: 18, repeat: Infinity, ease: "easeInOut" }}
      />

      <nav className="relative mx-auto flex max-w-6xl items-center justify-between px-4 py-6 sm:px-6">
        <Link href="/" className="flex items-center gap-2.5">
          <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary text-primary-foreground">
            <MessageSquareText className="h-5 w-5" />
          </span>
          <span className="text-[15px] font-semibold tracking-tight">Chatbotistic</span>
        </Link>
        <div className="flex items-center gap-2">
          <Link
            href="/docs"
            className="rounded-xl px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
          >
            Docs
          </Link>
          <ThemeToggle />
          <Link href="/login">
            <Button variant="ghost">Sign in</Button>
          </Link>
          <Link href="/signup">
            <Button>Get started</Button>
          </Link>
        </div>
      </nav>

      <div className="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6 sm:py-28">
        <motion.h1
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, ease: "easeOut" }}
          className="text-4xl font-semibold tracking-tight text-balance sm:text-5xl"
        >
          Turn WhatsApp conversations into revenue.
        </motion.h1>
        <motion.p
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.08, ease: "easeOut" }}
          className="mx-auto mt-4 max-w-xl text-lg text-muted-foreground"
        >
          Widgets, leads, bookings, and campaigns — one dashboard for every
          WhatsApp conversation your business has.
        </motion.p>
        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.16, ease: "easeOut" }}
          className="mt-8 flex flex-wrap items-center justify-center gap-3"
        >
          <Link href="/signup">
            <Button size="lg">
              Create your workspace
              <ArrowRight className="h-4 w-4" aria-hidden />
            </Button>
          </Link>
          <Link href="/login">
            <Button size="lg" variant="outline">
              Sign in
            </Button>
          </Link>
        </motion.div>
        <motion.p
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5, delay: 0.24 }}
          className="mt-6 text-sm text-muted-foreground"
        >
          Already a chatbotistic.com member? Use{" "}
          <span className="font-medium text-foreground">Open Dashboard</span>{" "}
          from your account menu to sign in automatically.{" "}
          <a
            href="https://www.chatbotistic.com/pricing/"
            className="font-medium text-primary hover:underline"
          >
            See plans &amp; pricing
          </a>
        </motion.p>
      </div>
    </header>
  );
}
