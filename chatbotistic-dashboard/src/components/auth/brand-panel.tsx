"use client";

import { motion } from "framer-motion";
import { MessageSquareText } from "lucide-react";

/** Left-hand split-screen panel for the auth pages — animated gradient
 * blobs behind the wordmark + tagline. Purely decorative. */
export function AuthBrandPanel() {
  return (
    <div className="relative hidden h-full flex-col justify-between overflow-hidden bg-[linear-gradient(160deg,var(--primary)_0%,#0f8a3f_60%,#0a5f2c_100%)] p-10 text-white lg:flex">
      <motion.div
        aria-hidden
        className="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-white/10 blur-3xl"
        animate={{ x: [0, 30, 0], y: [0, 20, 0] }}
        transition={{ duration: 14, repeat: Infinity, ease: "easeInOut" }}
      />
      <motion.div
        aria-hidden
        className="pointer-events-none absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-black/10 blur-3xl"
        animate={{ x: [0, -20, 0], y: [0, -30, 0] }}
        transition={{ duration: 16, repeat: Infinity, ease: "easeInOut" }}
      />

      <motion.div
        initial={{ opacity: 0, y: -8 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4, ease: "easeOut" }}
        className="relative flex items-center gap-2.5"
      >
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 backdrop-blur">
          <MessageSquareText className="h-5 w-5" />
        </span>
        <span className="text-lg font-semibold tracking-tight">Chatbotistic</span>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, y: 12 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.1, ease: "easeOut" }}
        className="relative max-w-md"
      >
        <h2 className="text-3xl font-semibold leading-tight tracking-tight">
          Turn WhatsApp conversations into revenue.
        </h2>
        <p className="mt-3 text-sm text-white/80">
          Widgets, landing pages, bookings, and campaigns — one dashboard for
          every conversation your business has.
        </p>
      </motion.div>
    </div>
  );
}
